<?php
/**
 * Tales of Souls
 *
 * PHP Version 8.2
 *
 * @package   Application\Frontend\Provider
 * @version   1.0.0
 */
declare(strict_types=1);

namespace Application\Frontend\Provider;

use Models\Account;
use Models\Feedback;
use Models\FeedbackMapper;
use Models\FeedbackState;
use Models\FeedbackType;

/**
 * Feedback board incl. comments and votes
 *
 * @package Application\Frontend\Provider
 * @since   1.0.0
 */
final class FeedbackData extends DataAbstract
{
    /**
     * Feedback states (key => title)
     *
     * @var array<string, string>
     * @since 1.0.0
     */
    public const STATES = [
        FeedbackState::OPEN     => 'Open',
        FeedbackState::PLANNED  => 'Planned',
        FeedbackState::PROGRESS => 'In progress',
        FeedbackState::DONE     => 'Done',
        FeedbackState::CLOSED   => 'Declined',
    ];

    /**
     * Feedback types
     *
     * @var string[]
     * @since 1.0.0
     */
    public const TYPES = [
        FeedbackType::BUG, FeedbackType::SUGGESTION, FeedbackType::BALANCE, FeedbackType::WEBSITE, FeedbackType::OTHER,
    ];

    /**
     * Author as array for the templates
     *
     * @param Account $account Account
     *
     * @return array{id:int, name:string, link:string, developer:bool}
     *
     * @since 1.0.0
     */
    public static function author(Account $account) : array
    {
        return [
            'id'        => $account->id,
            'name'      => $account->id === 0 ? 'Deleted account' : $account->name,
            'link'      => $account->id === 0 || $account->isDeveloper() ? '' : '/player/profile/' . $account->id,
            'developer' => $account->isDeveloper(),
        ];
    }

    /**
     * Filtered and sorted feedback list
     *
     * @param string $query Search text
     * @param string $type  Type filter
     * @param string $state State filter
     * @param string $sort  top|new|comments
     *
     * @return array<int, array>
     *
     * @since 1.0.0
     */
    public function getList(string $query, string $type, string $state, string $sort) : array
    {
        $where  = [];
        $params = [];

        if ($query !== '') {
            $where[]  = '(f.feedback_title LIKE ? OR f.feedback_text LIKE ?)';
            $params[] = '%' . $query . '%';
            $params[] = '%' . $query . '%';
        }

        if ($type !== '') {
            $where[]  = 'f.feedback_type = ?';
            $params[] = $type;
        }

        if ($state !== '') {
            $where[]  = 'f.feedback_state = ?';
            $params[] = $state;
        }

        $order = match ($sort) {
            'new'      => 'f.feedback_created DESC',
            'comments' => 'comment_count DESC, f.feedback_created DESC',
            default    => 'score DESC, f.feedback_created DESC',
        };

        $rows = $this->rows(
            'SELECT f.*, a.account_name, a.account_role,
                (SELECT COALESCE(SUM(CASE WHEN v.feedback_vote_value > 0 THEN 1 ELSE 0 END), 0) FROM feedback_vote v WHERE v.feedback_vote_feedback = f.feedback_id) AS up,
                (SELECT COALESCE(SUM(CASE WHEN v.feedback_vote_value < 0 THEN 1 ELSE 0 END), 0) FROM feedback_vote v WHERE v.feedback_vote_feedback = f.feedback_id) AS down,
                (SELECT COALESCE(SUM(v.feedback_vote_value), 0) FROM feedback_vote v WHERE v.feedback_vote_feedback = f.feedback_id) AS score,
                (SELECT COUNT(*) FROM feedback_comment c WHERE c.feedback_comment_feedback = f.feedback_id) AS comment_count
            FROM feedback f
            LEFT JOIN account a ON a.account_id = f.feedback_account'
            . (empty($where) ? '' : ' WHERE ' . \implode(' AND ', $where))
            . ' ORDER BY ' . $order,
            $params
        );

        $list = [];
        foreach ($rows as $row) {
            $list[] = [
                'id'            => (int) $row['feedback_id'],
                'date'          => new \DateTime($row['feedback_created']),
                'state'         => $row['feedback_state'],
                'state_title'   => self::STATES[$row['feedback_state']] ?? $row['feedback_state'],
                'type'          => $row['feedback_type'],
                'title'         => $row['feedback_title'],
                'author'        => ['name' => $row['account_name'] ?? 'Deleted account'],
                'up'            => (int) $row['up'],
                'down'          => (int) $row['down'],
                'score'         => (int) $row['score'],
                'comment_count' => (int) $row['comment_count'],
            ];
        }

        return $list;
    }

    /**
     * Single feedback incl. comments, votes and the votes of the viewer
     *
     * @param int $id      Feedback id
     * @param int $account Account id of the viewer (0 = guest)
     *
     * @return null|array
     *
     * @since 1.0.0
     */
    public function get(int $id, int $account) : ?array
    {
        /** @var Feedback $feedback */
        $feedback = FeedbackMapper::get()
            ->with('account')
            ->with('responseAccount')
            ->with('comments')
            ->with('comments/account')
            ->with('history')
            ->where('id', $id)
            ->execute();

        if ($feedback->id === 0) {
            return null;
        }

        $votes = $this->rows(
            'SELECT
                COALESCE(SUM(CASE WHEN feedback_vote_value > 0 THEN 1 ELSE 0 END), 0) AS up,
                COALESCE(SUM(CASE WHEN feedback_vote_value < 0 THEN 1 ELSE 0 END), 0) AS down,
                MAX(CASE WHEN feedback_vote_account = ? THEN feedback_vote_value END) AS own
            FROM feedback_vote WHERE feedback_vote_feedback = ?',
            [$account, $id]
        )[0];

        $commentIds   = \array_map(fn ($c) => $c->id, $feedback->comments);
        $commentVotes = [];
        if (!empty($commentIds)) {
            foreach ($this->rows(
                'SELECT feedback_comment_vote_comment AS id, COALESCE(SUM(feedback_comment_vote_value), 0) AS score,
                    MAX(CASE WHEN feedback_comment_vote_account = ? THEN feedback_comment_vote_value END) AS own
                FROM feedback_comment_vote WHERE feedback_comment_vote_comment IN (' . self::in($commentIds) . ')
                GROUP BY feedback_comment_vote_comment',
                \array_merge([$account], $commentIds)
            ) as $row) {
                $commentVotes[(int) $row['id']] = $row;
            }
        }

        $comments = [];
        foreach ($feedback->comments as $comment) {
            $own        = $commentVotes[$comment->id]['own'] ?? null;
            $comments[] = [
                'id'     => $comment->id,
                'author' => self::author($comment->account),
                'date'   => $comment->created,
                'text'   => $comment->text,
                'score'  => (int) ($commentVotes[$comment->id]['score'] ?? 0),
                'vote'   => $own === null ? '' : ((int) $own > 0 ? 'up' : 'down'),
            ];
        }

        \usort($comments, fn ($a, $b) => $a['date'] <=> $b['date']);

        $history = \array_map(fn ($h) => ['date' => $h->datetime->format('Y-m-d'), 'state' => $h->state, 'title' => self::STATES[$h->state] ?? $h->state], \array_values($feedback->history));
        \usort($history, fn ($a, $b) => $a['date'] <=> $b['date']);

        $lastActivity = \max(
            $feedback->updated ?? $feedback->created,
            $feedback->created,
            empty($comments) ? $feedback->created : \end($comments)['date']
        );

        return [
            'id'            => $feedback->id,
            'date'          => $feedback->created,
            'state'         => $feedback->state,
            'state_title'   => self::STATES[$feedback->state] ?? $feedback->state,
            'type'          => $feedback->type,
            'title'         => $feedback->title,
            'text'          => $feedback->text,
            'author'        => self::author($feedback->account),
            'up'            => (int) $votes['up'],
            'down'          => (int) $votes['down'],
            'score'         => (int) $votes['up'] - (int) $votes['down'],
            'vote'          => $votes['own'] === null ? '' : ((int) $votes['own'] > 0 ? 'up' : 'down'),
            'comments'      => $comments,
            'comment_count' => \count($comments),
            'last_activity' => $lastActivity,
            'history'       => $history,
            'response'      => $feedback->response === null || $feedback->response === '' ? null : [
                'author' => self::author($feedback->responseAccount),
                'date'   => $feedback->responseDatetime ?? $feedback->created,
                'text'   => $feedback->response,
            ],
        ];
    }
}
