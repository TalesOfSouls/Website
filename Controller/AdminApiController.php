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

use Models\Account;
use Models\AccountMapper;
use Models\AccountRole;
use Models\AccountStatus;
use Models\DevelopmentArea;
use Models\DevelopmentAreaMapper;
use Models\Faq;
use Models\FaqMapper;
use Models\Milestone;
use Models\MilestoneMapper;
use Models\News;
use Models\NewsMapper;
use Models\NewsStatus;
use Models\NullAccount;
use Models\WikiPage;
use Models\WikiPageMapper;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Module\ModuleAbstract;

/**
 * Api of the administration (news, wiki, FAQ, development roadmap, accounts)
 *
 * All endpoints require an administrator account.
 *
 * @package Controller
 * @since   1.0.0
 */
final class AdminApiController extends ModuleAbstract
{
    use ApiResponseTrait;

    /**
     * Module name (used for the event names)
     *
     * @var string
     * @since 1.0.0
     */
    public const NAME = 'Admin';

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

    /**
     * Wiki modules (data blocks below an article, see Application/Frontend/tpl/wiki/module)
     *
     * @return string[]
     *
     * @since 1.0.0
     */
    public static function wikiModules() : array
    {
        $modules = [];
        foreach (\glob(__DIR__ . '/../Application/Frontend/tpl/wiki/module/*.tpl.php') ?: [] as $file) {
            $modules[] = \basename($file, '.tpl.php');
        }

        \sort($modules);

        return $modules;
    }

    /**
     * Create an url slug from a text
     *
     * @param string $text Text
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function slugify(string $text) : string
    {
        $slug = \strtolower(\trim($text));
        $slug = \preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';

        return \trim($slug, '-');
    }

    /**
     * Parse a date from a datetime-local input
     *
     * @param string $value Value
     *
     * @return null|\DateTime
     *
     * @since 1.0.0
     */
    private function parseDate(string $value) : ?\DateTime
    {
        if ($value === '') {
            return null;
        }

        $date = \DateTime::createFromFormat('Y-m-d\TH:i', $value)
            ?: \DateTime::createFromFormat('Y-m-d H:i:s', $value)
            ?: \DateTime::createFromFormat('!Y-m-d', $value);

        return $date === false ? null : $date;
    }

    /**
     * Saved (create: load the edit page, update: stay on the page)
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param bool             $created  Model was created
     * @param string           $url      Edit page of the model
     * @param string           $message  Message
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function saved(RequestAbstract $request, ResponseAbstract $response, bool $created, string $url, string $message = 'Saved.') : void
    {
        if ($created) {
            $this->respond($request, $response, NotificationLevel::OK, $message, ['uri' => $url, 'delay' => 0], $url, 'redirect');

            return;
        }

        $this->respond($request, $response, NotificationLevel::OK, $message, [], $url);
    }

    /* ======================================================================
       News
       ====================================================================== */

    /**
     * Create or update a news post
     *
     * Data: id (0 = new), title, slug, summary, image, content, status, published
     */
    public function apiNewsSave(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        $id = $request->getDataInt('id') ?? 0;

        /** @var News $news */
        $news = $id > 0 ? NewsMapper::get()->where('id', $id)->execute() : new News();
        if ($id > 0 && $news->id === 0) {
            $this->invalid($request, $response, 'This news post doesn\'t exist.');

            return;
        }

        $title   = \trim($request->getDataString('title') ?? '');
        $content = \trim($request->getDataString('content') ?? '');
        $slug    = self::slugify($request->getDataString('slug') ?? '');
        $slug    = $slug === '' ? (new \DateTime())->format('Y-m-d') . '-' . self::slugify($title) : $slug;
        $image   = \trim($request->getDataString('image') ?? '');
        $status  = $request->getDataString('status') ?? NewsStatus::DRAFT;
        $summary = \trim($request->getDataString('summary') ?? '');

        $error = match (true) {
            \mb_strlen($title) < 3 || \mb_strlen($title) > 200                            => 'The title needs 3 - 200 characters.',
            $content === ''                                                                => 'Please write the news post.',
            !NewsStatus::isValidValue($status)                                             => 'Invalid status.',
            $image !== '' && \preg_match('~^(/|https?://)[^\s"\'<>]*$~', $image) !== 1     => 'The image needs to be an url (e.g. /Application/Frontend/data/news/img/...).',
            NewsMapper::count()->where('slug', $slug)->where('id', $news->id, '!=')->executeCount() > 0 => 'This url is already used by another news post.',
            default                                                                        => null,
        };

        if ($error !== null) {
            $this->invalid($request, $response, $error);

            return;
        }

        if ($summary === '') {
            // first paragraph of the post
            foreach (\preg_split('/\R\R+/', $content) ?: [] as $block) {
                $block = \trim($block);
                if ($block !== '' && !\in_array($block[0], ['#', '!', '*', '-', '<'], true)) {
                    $summary = \mb_strimwidth(\strip_tags($block), 0, 220, '...');
                    break;
                }
            }
        }

        $created = $news->id === 0;

        $news->title     = $title;
        $news->slug      = $slug;
        $news->summary   = \mb_substr($summary, 0, 300);
        $news->image     = $image;
        $news->content   = $content;
        $news->status    = $status;
        $news->published = $this->parseDate($request->getDataString('published') ?? '') ?? ($created ? new \DateTime() : $news->published);
        $news->updated   = new \DateTime();

        if ($created) {
            $news->account = new NullAccount($request->header->account);
            $news->created = new \DateTime();
            $this->createModel($request->header->account, $news, NewsMapper::class, 'news', $request->getOrigin());
        } else {
            $news->account = new NullAccount($news->account->id);
            NewsMapper::update()->execute($news);
        }

        $this->saved($request, $response, $created, '/admin/news/' . $news->id, $created ? 'The news post has been created.' : 'The news post has been saved.');
    }

    /**
     * Delete a news post
     *
     * Data: id
     */
    public function apiNewsDelete(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        /** @var News $news */
        $news = NewsMapper::get()->where('id', $request->getDataInt('id') ?? 0)->execute();
        if ($news->id === 0) {
            $this->invalid($request, $response, 'This news post doesn\'t exist.');

            return;
        }

        $this->deleteModel($request->header->account, $news, NewsMapper::class, 'news', $request->getOrigin());

        $this->respond($request, $response, NotificationLevel::OK, 'The news post has been deleted.', ['uri' => '/admin/news', 'delay' => 0], '/admin/news', 'redirect');
    }

    /* ======================================================================
       Wiki
       ====================================================================== */

    /**
     * Create or update a wiki article
     *
     * Data: id (0 = new), title, slug, nav_title, nav_order, module, content
     */
    public function apiWikiSave(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        $id = $request->getDataInt('id') ?? 0;

        /** @var WikiPage $page */
        $page = $id > 0 ? WikiPageMapper::get()->where('id', $id)->execute() : new WikiPage();
        if ($id > 0 && $page->id === 0) {
            $this->invalid($request, $response, 'This article doesn\'t exist.');

            return;
        }

        $title    = \trim($request->getDataString('title') ?? '');
        $slug     = \strtolower(\trim($request->getDataString('slug') ?? '', " /\t\n"));
        $module   = $request->getDataString('module') ?? '';
        $navOrder = \trim($request->getDataString('nav_order') ?? '');

        $error = match (true) {
            \mb_strlen($title) < 2 || \mb_strlen($title) > 200                         => 'The title needs 2 - 200 characters.',
            \preg_match('~^[a-z0-9_-]+(/[a-z0-9_-]+)*$~', $slug) !== 1                 => 'The url may only contain a-z, 0-9, -, _ and /.',
            \in_array($slug, ['search', 'item'], true)                                 => 'This url is reserved.',
            $module !== '' && !\in_array($module, self::wikiModules(), true)           => 'Unknown module.',
            $navOrder !== '' && !\is_numeric($navOrder)                                => 'The navigation position needs to be a number.',
            WikiPageMapper::count()->where('slug', $slug)->where('id', $page->id, '!=')->executeCount() > 0 => 'This url is already used by another article.',
            default                                                                    => null,
        };

        if ($error !== null) {
            $this->invalid($request, $response, $error);

            return;
        }

        $created = $page->id === 0;

        $page->title    = $title;
        $page->slug     = $slug;
        $page->navTitle = \trim($request->getDataString('nav_title') ?? '');
        $page->navOrder = $navOrder === '' ? null : (int) $navOrder;
        $page->module   = $module === '' ? null : $module;
        $page->content  = \trim($request->getDataString('content') ?? '') . "\n";
        $page->account  = new NullAccount($request->header->account);
        $page->updated  = new \DateTime();

        if ($created) {
            $this->createModel($request->header->account, $page, WikiPageMapper::class, 'wiki', $request->getOrigin());
        } else {
            WikiPageMapper::update()->execute($page);
        }

        $this->saved($request, $response, $created, '/admin/wiki/' . $page->id, $created ? 'The article has been created.' : 'The article has been saved.');
    }

    /**
     * Delete a wiki article
     *
     * Data: id
     */
    public function apiWikiDelete(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        /** @var WikiPage $page */
        $page = WikiPageMapper::get()->where('id', $request->getDataInt('id') ?? 0)->execute();
        if ($page->id === 0 || $page->slug === 'index') {
            $this->invalid($request, $response, 'This article can\'t be deleted.');

            return;
        }

        $this->deleteModel($request->header->account, $page, WikiPageMapper::class, 'wiki', $request->getOrigin());

        $this->respond($request, $response, NotificationLevel::OK, 'The article has been deleted.', ['uri' => '/admin/wiki', 'delay' => 0], '/admin/wiki', 'redirect');
    }

    /* ======================================================================
       FAQ
       ====================================================================== */

    /**
     * Create or update a FAQ entry
     *
     * Data: id (0 = new), question, answer, category, order, milestone
     */
    public function apiFaqSave(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        $id = $request->getDataInt('id') ?? 0;

        /** @var Faq $faq */
        $faq = $id > 0 ? FaqMapper::get()->where('id', $id)->execute() : new Faq();
        if ($id > 0 && $faq->id === 0) {
            $this->invalid($request, $response, 'This question doesn\'t exist.');

            return;
        }

        $question = \trim($request->getDataString('question') ?? '');
        $answer   = \trim($request->getDataString('answer') ?? '');
        $category = \trim($request->getDataString('category') ?? '');

        $error = match (true) {
            \mb_strlen($question) < 5 || \mb_strlen($question) > 300 => 'The question needs 5 - 300 characters.',
            $answer === ''                                           => 'Please write an answer.',
            $category === '' || \mb_strlen($category) > 50           => 'Please enter a category.',
            default                                                  => null,
        };

        if ($error !== null) {
            $this->invalid($request, $response, $error);

            return;
        }

        $created = $faq->id === 0;

        $faq->question  = $question;
        $faq->answer    = $answer;
        $faq->category  = $category;
        $faq->order     = $request->getDataInt('order') ?? 0;
        $faq->milestone = \trim($request->getDataString('milestone') ?? '') ?: 'Development';

        if ($created) {
            $faq->datetime = new \DateTime();
            $this->createModel($request->header->account, $faq, FaqMapper::class, 'faq', $request->getOrigin());
        } else {
            FaqMapper::update()->execute($faq);
        }

        $this->saved($request, $response, $created, '/admin/faq/' . $faq->id, $created ? 'The question has been created.' : 'The question has been saved.');
    }

    /**
     * Delete a FAQ entry
     *
     * Data: id
     */
    public function apiFaqDelete(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        /** @var Faq $faq */
        $faq = FaqMapper::get()->where('id', $request->getDataInt('id') ?? 0)->execute();
        if ($faq->id === 0) {
            $this->invalid($request, $response, 'This question doesn\'t exist.');

            return;
        }

        $this->deleteModel($request->header->account, $faq, FaqMapper::class, 'faq', $request->getOrigin());

        $this->respond($request, $response, NotificationLevel::OK, 'The question has been deleted.', ['uri' => '/admin/faq', 'delay' => 0], '/admin/faq', 'redirect');
    }

    /* ======================================================================
       Development roadmap
       ====================================================================== */

    /**
     * Create or update a milestone
     *
     * Data: id (0 = new), title, description, state, when, order
     */
    public function apiMilestoneSave(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        $id = $request->getDataInt('id') ?? 0;

        /** @var Milestone $milestone */
        $milestone = $id > 0 ? MilestoneMapper::get()->where('id', $id)->execute() : new Milestone();
        $title     = \trim($request->getDataString('title') ?? '');
        $state     = $request->getDataString('state') ?? '';

        if (($id > 0 && $milestone->id === 0) || $title === '' || !\in_array($state, ['done', 'progress', 'planned'], true)) {
            $this->invalid($request, $response, 'Please enter a title and a state.');

            return;
        }

        $created = $milestone->id === 0;

        $milestone->title       = \mb_substr($title, 0, 100);
        $milestone->description = \mb_substr(\trim($request->getDataString('description') ?? ''), 0, 500);
        $milestone->state       = $state;
        $milestone->when        = \mb_substr(\trim($request->getDataString('when') ?? ''), 0, 30);
        $milestone->order       = $request->getDataInt('order') ?? 0;

        $created
            ? MilestoneMapper::create()->execute($milestone)
            : MilestoneMapper::update()->execute($milestone);

        $this->saved($request, $response, $created, '/admin/development', $created ? 'The milestone has been added.' : 'The milestone has been saved.');
    }

    /**
     * Delete a milestone
     *
     * Data: id
     */
    public function apiMilestoneDelete(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        /** @var Milestone $milestone */
        $milestone = MilestoneMapper::get()->where('id', $request->getDataInt('id') ?? 0)->execute();
        if ($milestone->id > 0) {
            MilestoneMapper::delete()->execute($milestone);
        }

        $this->respond($request, $response, NotificationLevel::OK, 'The milestone has been deleted.', ['uri' => '/admin/development', 'delay' => 0], '/admin/development', 'redirect');
    }

    /**
     * Create or update a development area
     *
     * Data: id (0 = new), name, progress, order
     */
    public function apiAreaSave(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        $id = $request->getDataInt('id') ?? 0;

        /** @var DevelopmentArea $area */
        $area     = $id > 0 ? DevelopmentAreaMapper::get()->where('id', $id)->execute() : new DevelopmentArea();
        $name     = \trim($request->getDataString('name') ?? '');
        $progress = $request->getDataInt('progress') ?? -1;

        if (($id > 0 && $area->id === 0) || $name === '' || $progress < 0 || $progress > 100) {
            $this->invalid($request, $response, 'Please enter a name and a progress between 0 and 100.');

            return;
        }

        $created = $area->id === 0;

        $area->name     = \mb_substr($name, 0, 100);
        $area->progress = $progress;
        $area->order    = $request->getDataInt('order') ?? 0;

        $created
            ? DevelopmentAreaMapper::create()->execute($area)
            : DevelopmentAreaMapper::update()->execute($area);

        $this->saved($request, $response, $created, '/admin/development', $created ? 'The area has been added.' : 'The area has been saved.');
    }

    /**
     * Delete a development area
     *
     * Data: id
     */
    public function apiAreaDelete(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        /** @var DevelopmentArea $area */
        $area = DevelopmentAreaMapper::get()->where('id', $request->getDataInt('id') ?? 0)->execute();
        if ($area->id > 0) {
            DevelopmentAreaMapper::delete()->execute($area);
        }

        $this->respond($request, $response, NotificationLevel::OK, 'The area has been deleted.', ['uri' => '/admin/development', 'delay' => 0], '/admin/development', 'redirect');
    }

    /* ======================================================================
       Accounts
       ====================================================================== */

    /**
     * Change role and status of an account
     *
     * Data: id, role, status
     */
    public function apiAccountRoleSave(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!$this->requireRole($request, $response)) {
            return;
        }

        /** @var Account $account */
        $account = AccountMapper::get()->where('id', $request->getDataInt('id') ?? 0)->execute();
        $role    = $request->getDataInt('role') ?? -1;
        $status  = $request->getDataInt('status') ?? -1;

        if ($account->id === 0 || !AccountRole::isValidValue($role) || !AccountStatus::isValidValue($status)) {
            $this->invalid($request, $response, 'Invalid account, role or status.');

            return;
        }

        if ($account->id === $request->header->account && ($role !== $account->role || $status !== $account->status)) {
            $this->respond($request, $response, NotificationLevel::WARNING, 'You can\'t change your own role or status.', [], null, null, RequestStatusCode::R_400);

            return;
        }

        $account->role   = $role;
        $account->status = $status;
        AccountMapper::update()->execute($account);

        $this->respond($request, $response, NotificationLevel::OK, $account->name . ' has been saved.', [], '/admin/users');
    }
}
