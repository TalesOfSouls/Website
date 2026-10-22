<?php
/**
 * Tales of Souls
 *
 * PHP Version 8.2
 *
 * @package   Controller
 * @version   1.0.0
 */
declare(strict_types=1);

namespace Controller;

use Application\Frontend\Provider\CharacterData;
use Application\Frontend\Provider\ProfileData;
use Models\Account;
use Models\AccountMapper;
use Models\AccountRole;
use Models\AccountStatus;
use Models\ContactMessage;
use Models\ContactMessageMapper;
use Models\Feedback;
use Models\FeedbackComment;
use Models\FeedbackCommentMapper;
use Models\FeedbackCommentVote;
use Models\FeedbackCommentVoteMapper;
use Models\FeedbackMapper;
use Models\FeedbackState;
use Models\FeedbackStateLog;
use Models\FeedbackStateLogMapper;
use Models\FeedbackType;
use Models\FeedbackVote;
use Models\FeedbackVoteMapper;
use Models\NullAccount;
use phpOMS\Auth\Auth;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Module\ModuleAbstract;
use phpOMS\System\MimeType;

/**
 * Api controller
 *
 * Handles the data sent by the website (forms).
 *
 * Every endpoint answers in two ways:
 *  - jsOMS requests (XMLHttpRequest) receive JSON, either a notification
 *    {"status", "title", "message", "response"} or an action for the jsOMS response manager {"type", ...}
 *  - normal form posts (browser without JavaScript) are redirected (303) back to the website,
 *    the message is stored in the session and shown on the next page (see tpl/header.php)
 *
 * @package Controller
 * @since   1.0.0
 */
final class ApiController extends ModuleAbstract
{
    use ApiResponseTrait;

    /**
     * Module name (used for the event names)
     *
     * @var string
     * @since 1.0.0
     */
    public const NAME = 'Api';

    /**
     * Providing.
     *
     * @var string[]
     * @since 1.0.0
     */
    public static array $providing = [];

    /**
     * Dependencies.
     *
     * @var string[]
     * @since 1.0.0
     */
    public static array $dependencies = [];

    /* ======================================================================
       Account
       ====================================================================== */

    /**
     * Sign in
     *
     * Data: user (name or e-mail), password, redirect (optional)
     */
    public function apiAccountSignin(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $user     = \trim($request->getDataString('user') ?? '');
        $password = $request->getDataString('password') ?? '';

        if ($user === '' || $password === '') {
            $this->invalid($request, $response, 'Please enter your user name and password.');

            return;
        }

        /** @var Account $account */
        $account = AccountMapper::get()
            ->where('name', $user)
            ->where('email', \strtolower($user), '=', 'OR')
            ->execute();

        if ($account->id === 0
            || $account->password === ''
            || !\password_verify($password, $account->password)
        ) {
            $this->invalid($request, $response, 'Wrong user name or password.');

            return;
        }

        if ($account->status !== AccountStatus::ACTIVE) {
            $this->invalid($request, $response, 'This account is not active.');

            return;
        }

        if (\password_needs_rehash($account->password, \PASSWORD_DEFAULT)) {
            $account->password = \password_hash($password, \PASSWORD_DEFAULT);
        }

        $account->lastLogin = new \DateTime();
        AccountMapper::update()->execute($account);

        $this->app->sessionManager->set('UID', $account->id, true);

        $home   = $account->isAdmin() ? '/admin' : '/player/profile/' . $account->id;
        $target = $this->redirectTarget($request, $home);
        if ($target === '/signin' || \str_starts_with($target, '/signin?') || \str_starts_with($target, '/signup')) {
            $target = $home;
        }

        $this->respond(
            $request, $response, NotificationLevel::OK, 'Welcome back, ' . $account->name . '!',
            ['uri' => $target, 'delay' => 0], $target, 'redirect'
        );
    }

    /**
     * Create an account
     *
     * Data: name, email, password, password_repeat, birthday, terms, newsletter (optional)
     *
     * @todo Send a confirmation mail once mails can be sent, the account is active right away for now
     */
    public function apiAccountSignup(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $name     = \trim($request->getDataString('name') ?? '');
        $email    = \strtolower(\trim($request->getDataString('email') ?? ''));
        $password = $request->getDataString('password') ?? '';
        $repeat   = $request->getDataString('password_repeat') ?? '';
        $birthday = \DateTime::createFromFormat('!Y-m-d', $request->getDataString('birthday') ?? '');

        $error = match (true) {
            \preg_match('/^[A-Za-z0-9_\-]{3,32}$/', $name) !== 1                  => 'The user name needs 3 - 32 characters (letters, numbers, - and _).',
            \filter_var($email, \FILTER_VALIDATE_EMAIL) === false                 => 'Please enter a valid e-mail address.',
            \mb_strlen($password) < 10                                            => 'The password needs at least 10 characters.',
            $password !== $repeat                                                 => 'The passwords don\'t match.',
            $birthday === false                                                   => 'Please enter your date of birth.',
            $birthday->diff(new \DateTime())->y < 13                              => 'You need to be at least 13 years old.',
            !$request->hasData('terms')                                           => 'Please accept the terms of service.',
            default                                                               => null,
        };

        if ($error !== null) {
            $this->invalid($request, $response, $error);

            return;
        }

        $exists = AccountMapper::count()
            ->where('name', $name)
            ->where('email', $email, '=', 'OR')
            ->executeCount();

        $reserved = (int) $this->app->dbPool->get()->con
            ->query('SELECT COUNT(*) FROM account_character_reserves WHERE LOWER(account_character_reserves_name) = ' . $this->app->dbPool->get()->con->quote(\strtolower($name)))
            ->fetchColumn();

        if ($exists > 0 || $reserved > 0) {
            $this->invalid($request, $response, 'This user name or e-mail address is already taken.');

            return;
        }

        $account           = new Account();
        $account->name     = $name;
        $account->email    = $email;
        $account->password = \password_hash($password, \PASSWORD_DEFAULT);
        $account->created  = new \DateTime();
        $account->status   = AccountStatus::ACTIVE;
        $account->role     = AccountRole::PLAYER;

        $this->createModel($request->header->account, $account, AccountMapper::class, 'account', $request->getOrigin());

        $this->app->sessionManager->set('UID', $account->id, true);

        $this->respond(
            $request, $response, NotificationLevel::OK, 'Welcome to Tales of Souls, ' . $account->name . '!',
            ['uri' => '/player/profile/' . $account->id, 'delay' => 0], '/player/profile/' . $account->id, 'redirect'
        );
    }

    /**
     * Sign out
     */
    public function apiAccountSignout(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        Auth::logout($this->app->sessionManager);
        $this->app->sessionManager->set('UID', 0, true);

        $this->respond($request, $response, NotificationLevel::OK, 'You are signed out.', ['uri' => '/', 'delay' => 0], '/', 'redirect');
    }

    /**
     * Forgot password
     *
     * Data: email
     *
     * @todo Send the reset mail once mails can be sent
     */
    public function apiAccountForgot(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $email = \trim($request->getDataString('email') ?? '');

        if (\filter_var($email, \FILTER_VALIDATE_EMAIL) === false) {
            $this->invalid($request, $response, 'Please enter a valid e-mail address.');

            return;
        }

        // the same answer for existing and unknown addresses (no account enumeration)
        $this->respond($request, $response, NotificationLevel::INFO, 'If an account exists for this e-mail address, you will receive a reset link shortly.');
    }

    /* ======================================================================
       Feedback
       ====================================================================== */

    /**
     * Create a feedback
     *
     * Data: type, title, description
     */
    public function apiFeedbackCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireAccount($request, $response)) {
            return;
        }

        $type  = $request->getDataString('type') ?? '';
        $title = \trim($request->getDataString('title') ?? '');
        $text  = \trim($request->getDataString('description') ?? '');

        $error = match (true) {
            !FeedbackType::isValidValue($type)                         => 'Please select a type.',
            \mb_strlen($title) < 5 || \mb_strlen($title) > 120        => 'The title needs 5 - 120 characters.',
            \mb_strlen($text) < 20 || \mb_strlen($text) > 5000         => 'The description needs 20 - 5000 characters.',
            default                                                    => null,
        };

        if ($error !== null) {
            $this->invalid($request, $response, $error);

            return;
        }

        $feedback          = new Feedback();
        $feedback->account = new NullAccount($request->header->account);
        $feedback->type    = $type;
        $feedback->state   = FeedbackState::OPEN;
        $feedback->title   = $title;
        $feedback->text    = $text;
        $feedback->created = new \DateTime();
        $feedback->updated = new \DateTime();

        $this->createModel($request->header->account, $feedback, FeedbackMapper::class, 'feedback', $request->getOrigin());

        $log           = new FeedbackStateLog();
        $log->feedback = $feedback->id;
        $log->state    = FeedbackState::OPEN;
        $log->datetime = new \DateTime();
        FeedbackStateLogMapper::create()->execute($log);

        // the author supports the own feedback
        $vote           = new FeedbackVote();
        $vote->feedback = $feedback->id;
        $vote->account  = $request->header->account;
        $vote->value    = 1;
        $vote->datetime = new \DateTime();
        FeedbackVoteMapper::create()->execute($vote);

        $url = '/feedback/' . $feedback->id;
        $this->respond(
            $request, $response, NotificationLevel::OK, 'Thank you for your feedback!',
            ['uri' => $url, 'delay' => 0, 'id' => $feedback->id], $url, 'redirect'
        );
    }

    /**
     * Vote on a feedback (voting the same again removes the vote)
     *
     * Data: feedback, vote (up|down)
     */
    public function apiFeedbackVote(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireAccount($request, $response)) {
            return;
        }

        $feedbackId = $request->getDataInt('feedback') ?? 0;
        $direction  = $request->getDataString('vote') ?? '';

        if (!\in_array($direction, ['up', 'down'], true)
            || FeedbackMapper::count()->where('id', $feedbackId)->executeCount() === 0
        ) {
            $this->invalid($request, $response, 'Invalid vote.');

            return;
        }

        /** @var FeedbackVote $vote */
        $vote  = FeedbackVoteMapper::get()->where('feedback', $feedbackId)->where('account', $request->header->account)->execute();
        $value = $direction === 'up' ? 1 : -1;

        if ($vote->id > 0 && $vote->value === $value) {
            FeedbackVoteMapper::delete()->execute($vote);
            $current = '';
        } else {
            // the mapper returns a null model if no vote exists yet, null models are never created
            $vote           = $vote->id > 0 ? $vote : new FeedbackVote();
            $vote->feedback = $feedbackId;
            $vote->account  = $request->header->account;
            $vote->value    = $value;
            $vote->datetime = new \DateTime();

            $vote->id > 0
                ? FeedbackVoteMapper::update()->execute($vote)
                : FeedbackVoteMapper::create()->execute($vote);

            $current = $direction;
        }

        $counts = $this->app->dbPool->get()->con->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN feedback_vote_value > 0 THEN 1 ELSE 0 END), 0) AS up,
                COALESCE(SUM(CASE WHEN feedback_vote_value < 0 THEN 1 ELSE 0 END), 0) AS down
            FROM feedback_vote WHERE feedback_vote_feedback = ?'
        );
        $counts->execute([$feedbackId]);
        $row = $counts->fetch(\PDO::FETCH_ASSOC);

        $this->respond(
            $request, $response, NotificationLevel::OK,
            $current === '' ? 'Your vote was removed.' : 'Thank you for your vote.',
            [
                'target' => 'feedback-' . $feedbackId,
                'vote'   => $current,
                'up'     => (int) $row['up'],
                'down'   => (int) $row['down'],
                'score'  => (int) $row['up'] - (int) $row['down'],
            ],
            null,
            'vote'
        );
    }

    /**
     * Comment on a feedback
     *
     * Data: feedback, text
     */
    public function apiFeedbackCommentCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireAccount($request, $response)) {
            return;
        }

        $feedbackId = $request->getDataInt('feedback') ?? 0;
        $text       = \trim($request->getDataString('text') ?? '');

        if (FeedbackMapper::count()->where('id', $feedbackId)->executeCount() === 0) {
            $this->invalid($request, $response, 'This feedback doesn\'t exist.');

            return;
        }

        if (\mb_strlen($text) < 2 || \mb_strlen($text) > 2000) {
            $this->invalid($request, $response, 'Please write a comment (max. 2000 characters).');

            return;
        }

        $comment           = new FeedbackComment();
        $comment->feedback = $feedbackId;
        $comment->account  = new NullAccount($request->header->account);
        $comment->text     = $text;
        $comment->created  = new \DateTime();

        $this->createModel($request->header->account, $comment, FeedbackCommentMapper::class, 'feedback_comment', $request->getOrigin());

        $count = FeedbackCommentMapper::count()->where('feedback', $feedbackId)->executeCount();

        $this->respond(
            $request, $response, NotificationLevel::OK, 'Your comment has been posted.',
            [
                'id'     => $comment->id,
                'author' => $this->app->accountManager->get($request->header->account)->login ?? '',
                'date'   => $comment->created->format('Y-m-d H:i'),
                'text'   => $text,
                'count'  => $count,
            ],
            '/feedback/' . $feedbackId . '#comment-' . $comment->id,
            'comment'
        );
    }

    /**
     * Vote on a comment (voting the same again removes the vote)
     *
     * Data: comment, vote (up|down)
     */
    public function apiFeedbackCommentVote(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireAccount($request, $response)) {
            return;
        }

        $commentId = $request->getDataInt('comment') ?? 0;
        $direction = $request->getDataString('vote') ?? '';

        if (!\in_array($direction, ['up', 'down'], true)
            || FeedbackCommentMapper::count()->where('id', $commentId)->executeCount() === 0
        ) {
            $this->invalid($request, $response, 'Invalid vote.');

            return;
        }

        /** @var FeedbackCommentVote $vote */
        $vote  = FeedbackCommentVoteMapper::get()->where('comment', $commentId)->where('account', $request->header->account)->execute();
        $value = $direction === 'up' ? 1 : -1;

        if ($vote->id > 0 && $vote->value === $value) {
            FeedbackCommentVoteMapper::delete()->execute($vote);
            $current = '';
        } else {
            $vote           = $vote->id > 0 ? $vote : new FeedbackCommentVote();
            $vote->comment  = $commentId;
            $vote->account  = $request->header->account;
            $vote->value    = $value;
            $vote->datetime = new \DateTime();

            $vote->id > 0
                ? FeedbackCommentVoteMapper::update()->execute($vote)
                : FeedbackCommentVoteMapper::create()->execute($vote);

            $current = $direction;
        }

        $stmt = $this->app->dbPool->get()->con->prepare(
            'SELECT COALESCE(SUM(feedback_comment_vote_value), 0) FROM feedback_comment_vote WHERE feedback_comment_vote_comment = ?'
        );
        $stmt->execute([$commentId]);

        $this->respond(
            $request, $response, NotificationLevel::OK,
            $current === '' ? 'Your vote was removed.' : 'Thank you for your vote.',
            ['target' => 'comment-' . $commentId, 'vote' => $current, 'score' => (int) $stmt->fetchColumn()],
            null,
            'vote'
        );
    }

    /**
     * Update the own feedback (only while it is open, the dev team can always edit)
     *
     * Data: feedback, title, description
     */
    public function apiFeedbackUpdate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireAccount($request, $response)) {
            return;
        }

        /** @var Feedback $feedback */
        $feedback = FeedbackMapper::get()->where('id', $request->getDataInt('feedback') ?? 0)->execute();
        $account  = $this->currentAccount($request);

        if ($feedback->id === 0
            || !($account->isDeveloper() || ($feedback->account->id === $account->id && $feedback->state === FeedbackState::OPEN))
        ) {
            $this->respond($request, $response, NotificationLevel::ERROR, 'You can only edit your own open feedback.', [], null, null, RequestStatusCode::R_403);

            return;
        }

        $title = \trim($request->getDataString('title') ?? '');
        $text  = \trim($request->getDataString('description') ?? '');

        if (\mb_strlen($title) < 5 || \mb_strlen($title) > 120) {
            $this->invalid($request, $response, 'The title needs 5 - 120 characters.');

            return;
        }

        if (\mb_strlen($text) < 20 || \mb_strlen($text) > 5000) {
            $this->invalid($request, $response, 'The description needs 20 - 5000 characters.');

            return;
        }

        $feedback->title   = $title;
        $feedback->text    = $text;
        $feedback->updated = new \DateTime();
        FeedbackMapper::update()->execute($feedback);

        $url = '/feedback/' . $feedback->id;
        $this->respond($request, $response, NotificationLevel::OK, 'Your feedback has been updated.', ['uri' => $url, 'delay' => 0], $url, 'redirect');
    }

    /**
     * Delete a comment (own comments, the dev team can delete all comments)
     *
     * Data: comment
     */
    public function apiFeedbackCommentDelete(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireAccount($request, $response)) {
            return;
        }

        /** @var FeedbackComment $comment */
        $comment = FeedbackCommentMapper::get()->where('id', $request->getDataInt('comment') ?? 0)->execute();
        $account = $this->currentAccount($request);

        if ($comment->id === 0 || !($comment->account->id === $account->id || $account->isDeveloper())) {
            $this->respond($request, $response, NotificationLevel::ERROR, 'You can only delete your own comments.', [], null, null, RequestStatusCode::R_403);

            return;
        }

        $con  = $this->app->dbPool->get()->con;
        $stmt = $con->prepare('DELETE FROM feedback_comment_vote WHERE feedback_comment_vote_comment = ?');
        $stmt->execute([$comment->id]);

        $this->deleteModel($request->header->account, $comment, FeedbackCommentMapper::class, 'feedback_comment', $request->getOrigin());

        $this->respond(
            $request, $response, NotificationLevel::OK, 'The comment has been deleted.',
            ['id' => $comment->id, 'count' => FeedbackCommentMapper::count()->where('feedback', $comment->feedback)->executeCount()],
            '/feedback/' . $comment->feedback . '#comments',
            'commentDelete'
        );
    }

    /**
     * Change the state and the developer response of a feedback (dev team)
     *
     * Data: feedback, state, response
     */
    public function apiFeedbackModerate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response, false)) {
            return;
        }

        /** @var Feedback $feedback */
        $feedback = FeedbackMapper::get()->with('account')->where('id', $request->getDataInt('feedback') ?? 0)->execute();
        $state    = $request->getDataString('state') ?? '';

        if ($feedback->id === 0 || !FeedbackState::isValidValue($state)) {
            $this->invalid($request, $response, 'Invalid feedback or state.');

            return;
        }

        $text = \trim($request->getDataString('response') ?? '');

        if ($state !== $feedback->state) {
            $log           = new FeedbackStateLog();
            $log->feedback = $feedback->id;
            $log->state    = $state;
            $log->datetime = new \DateTime();
            FeedbackStateLogMapper::create()->execute($log);
        }

        if ($text !== ($feedback->response ?? '')) {
            $feedback->response         = $text === '' ? null : $text;
            $feedback->responseAccount  = new NullAccount($text === '' ? 0 : $request->header->account);
            $feedback->responseDatetime = $text === '' ? null : new \DateTime();
        }

        $feedback->state   = $state;
        $feedback->updated = new \DateTime();
        FeedbackMapper::update()->execute($feedback);

        $url = '/feedback/' . $feedback->id;
        $this->respond($request, $response, NotificationLevel::OK, 'The feedback has been updated.', ['uri' => $url, 'delay' => 0], $url, 'redirect');
    }

    /**
     * Delete a feedback incl. votes and comments (dev team)
     *
     * Data: feedback
     */
    public function apiFeedbackDelete(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response, false)) {
            return;
        }

        $id = $request->getDataInt('feedback') ?? 0;
        if (FeedbackMapper::count()->where('id', $id)->executeCount() === 0) {
            $this->invalid($request, $response, 'This feedback doesn\'t exist.');

            return;
        }

        $con = $this->app->dbPool->get()->con;
        $con->beginTransaction();

        foreach ([
            'DELETE FROM feedback_comment_vote WHERE feedback_comment_vote_comment IN (SELECT feedback_comment_id FROM feedback_comment WHERE feedback_comment_feedback = ?)',
            'DELETE FROM feedback_comment WHERE feedback_comment_feedback = ?',
            'DELETE FROM feedback_vote WHERE feedback_vote_feedback = ?',
            'DELETE FROM feedback_state_log WHERE feedback_state_log_feedback = ?',
            'DELETE FROM feedback WHERE feedback_id = ?',
        ] as $sql) {
            $stmt = $con->prepare($sql);
            $stmt->execute([$id]);
        }

        $con->commit();

        $this->respond($request, $response, NotificationLevel::OK, 'The feedback has been deleted.', ['uri' => '/feedback', 'delay' => 0], '/feedback', 'redirect');
    }

    /* ======================================================================
       Account settings
       ====================================================================== */

    /**
     * Change e-mail and/or password of the signed in account
     *
     * Data: current_password, email (optional), password + password_repeat (optional)
     */
    public function apiAccountUpdate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireAccount($request, $response)) {
            return;
        }

        $account = $this->currentAccount($request);
        $current = $request->getDataString('current_password') ?? '';

        if ($account->password === '' || !\password_verify($current, $account->password)) {
            $this->invalid($request, $response, 'Your current password is wrong.');

            return;
        }

        $email    = \strtolower(\trim($request->getDataString('email') ?? ''));
        $password = $request->getDataString('password') ?? '';
        $changes  = [];

        if ($email !== '' && $email !== $account->email) {
            if (\filter_var($email, \FILTER_VALIDATE_EMAIL) === false) {
                $this->invalid($request, $response, 'Please enter a valid e-mail address.');

                return;
            }

            if (AccountMapper::count()->where('email', $email)->executeCount() > 0) {
                $this->invalid($request, $response, 'This e-mail address is already used.');

                return;
            }

            $account->email = $email;
            $changes[]      = 'e-mail address';
        }

        if ($password !== '') {
            if (\mb_strlen($password) < 10) {
                $this->invalid($request, $response, 'The new password needs at least 10 characters.');

                return;
            }

            if ($password !== ($request->getDataString('password_repeat') ?? '')) {
                $this->invalid($request, $response, 'The new passwords don\'t match.');

                return;
            }

            $account->password = \password_hash($password, \PASSWORD_DEFAULT);
            $changes[]         = 'password';
        }

        if (empty($changes)) {
            $this->respond($request, $response, NotificationLevel::INFO, 'Nothing to change.');

            return;
        }

        AccountMapper::update()->execute($account);

        $this->respond($request, $response, NotificationLevel::OK, 'Your ' . \implode(' and ', $changes) . ' has been changed.', [], '/account');
    }

    /* ======================================================================
       Contact
       ====================================================================== */

    /**
     * Contact form
     *
     * Data: name, email, topic, message, privacy
     *
     * @todo Forward the message by mail once mails can be sent
     */
    public function apiContact(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $name    = \trim($request->getDataString('name') ?? '');
        $email   = \trim($request->getDataString('email') ?? '');
        $topic   = \trim($request->getDataString('topic') ?? '');
        $message = \trim($request->getDataString('message') ?? '');

        $error = match (true) {
            $name === '' || \mb_strlen($name) > 100                  => 'Please enter your name.',
            \filter_var($email, \FILTER_VALIDATE_EMAIL) === false    => 'Please enter a valid e-mail address.',
            \mb_strlen($message) < 10 || \mb_strlen($message) > 5000 => 'Please write a message (10 - 5000 characters).',
            !$request->hasData('privacy')                            => 'Please agree to the processing of your data.',
            default                                                  => null,
        };

        if ($error !== null) {
            $this->invalid($request, $response, $error);

            return;
        }

        $contact          = new ContactMessage();
        $contact->account = $request->header->account > 0 ? $request->header->account : null;
        $contact->name    = $name;
        $contact->email   = $email;
        $contact->subject = \mb_substr($topic, 0, 100);
        $contact->text    = $message;
        $contact->created = new \DateTime();

        ContactMessageMapper::create()->execute($contact);

        $this->respond($request, $response, NotificationLevel::OK, 'Thank you for your message! We usually answer within 1 - 3 days.', [], '/contact');
    }

    /* ======================================================================
       Read only endpoints
       ====================================================================== */

    /**
     * Character data
     *
     * Url: /api/character/{id}
     */
    public function apiCharacterGet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $profiles  = new ProfileData($this->app->dbPool->get());
        $character = $profiles->getCharacter((int) $request->uri->getPathElement(1));

        $response->header->set('Content-Type', MimeType::M_JSON . '; charset=utf-8', true);

        if ($character === null) {
            $response->header->status = RequestStatusCode::R_404;
            $response->data[$request->uri->__toString()] = ['status' => NotificationLevel::ERROR, 'title' => '', 'message' => 'Character not found.', 'response' => []];

            return;
        }

        $result              = $profiles->characterToArray($character);
        $result['created']   = $character->created?->format('c');
        $result['rank']      = $profiles->getRank($character);
        $result['equipment'] = (new CharacterData($this->app->dbPool->get()))->equipment($character->id);

        $response->data[$request->uri->__toString()] = ['status' => NotificationLevel::OK, 'title' => '', 'message' => '', 'response' => $result];
    }

    /**
     * Player data
     *
     * Url: /api/player/{id}
     */
    public function apiPlayerGet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $profiles = new ProfileData($this->app->dbPool->get());
        $account  = $profiles->getAccount((int) $request->uri->getPathElement(1));

        $response->header->set('Content-Type', MimeType::M_JSON . '; charset=utf-8', true);

        if ($account === null) {
            $response->header->status = RequestStatusCode::R_404;
            $response->data[$request->uri->__toString()] = ['status' => NotificationLevel::ERROR, 'title' => '', 'message' => 'Player not found.', 'response' => []];

            return;
        }

        $characters = \array_values(\array_map(function (array $c) {
            $c['created'] = $c['created']?->format('c');

            return $c;
        }, $profiles->getCharactersOfAccount($account->id)));

        $response->data[$request->uri->__toString()] = [
            'status'   => NotificationLevel::OK,
            'title'    => '',
            'message'  => '',
            'response' => [
                'id'         => $account->id,
                'name'       => $account->name,
                'created'    => $account->created?->format('c'),
                'developer'  => $account->isDeveloper(),
                'statistics' => $profiles->getAccountStats($account->id),
                'characters' => $characters,
            ],
        ];
    }
}
