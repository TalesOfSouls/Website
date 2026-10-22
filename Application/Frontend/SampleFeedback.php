<?php
/**
 * Tales of Souls
 *
 * PHP Version 8.2
 *
 * @package   Application\Frontend
 * @version   1.0.0
 */
declare(strict_types=1);

namespace Application\Frontend;

/**
 * Sample feedback board incl. comments and votes.
 *
 * @todo Replace with feedback / feedback_comment / feedback_vote tables
 */
final class SampleFeedback
{
    /**
     * Feedback states (key => title)
     */
    public const STATES = [
        'open'     => 'Open',
        'planned'  => 'Planned',
        'progress' => 'In progress',
        'done'     => 'Done',
        'closed'   => 'Declined',
    ];

    public const TYPES = ['Bug', 'Suggestion', 'Balance', 'Website', 'Other'];

    /**
     * Authors that are part of the dev team
     */
    private const DEVELOPERS = ['Spl1nes', 'Morgwen'];

    /**
     * Feedback entries
     *
     * author: player id of the sample profiles or a plain name, history: [date, state]
     */
    private const FEEDBACK = [
        1 => [
            'date' => '2026-10-02 18:24', 'state' => 'planned', 'type' => 'Suggestion', 'author' => 1002, 'up' => 141, 'down' => 13,
            'title' => 'Add a transmog system for all armor types',
            'text'  => "Right now the best armor often looks the worst. A transmog system would let us keep the stats of an item and use the look of another one we already collected.\n\nIdeally it works for all armor slots incl. shields and cloaks, and also lets us hide the helmet. The appearances could be unlocked when an item is looted, so there is a reason to farm old dungeons again.",
            'history'  => [['2026-10-02', 'open'], ['2026-10-05', 'planned']],
            'response' => ['author' => 'Spl1nes', 'date' => '2026-10-05 10:12', 'text' => "We like this a lot and put it on the roadmap. Appearances will be unlocked on loot and stored per account. Weapons will follow later because of the animation sets."],
        ],
        2 => [
            'date' => '2026-09-30 21:03', 'state' => 'progress', 'type' => 'Bug', 'author' => 1004, 'up' => 66, 'down' => 2,
            'title' => 'Pets sometimes get stuck on stairs',
            'text'  => "Pets get stuck on the stairs in Ironhold and in the Moonwood Hollow dungeon. They only teleport back once you are around 60 meters away, which is a problem during boss fights because they stop attacking.\n\nSteps to reproduce: summon any pet, walk up the stairs next to the Ironhold bank, the pet stays at the first step.",
            'history'  => [['2026-09-30', 'open'], ['2026-10-01', 'progress']],
            'response' => ['author' => 'Morgwen', 'date' => '2026-10-01 14:40', 'text' => "Thanks for the detailed steps. The navigation mesh is missing on some stairs, a fix is in testing."],
        ],
        3 => [
            'date' => '2026-09-28 12:51', 'state' => 'open', 'type' => 'Suggestion', 'author' => 1001, 'up' => 61, 'down' => 4,
            'title' => 'Guild bank permissions per rank',
            'text'  => "The guild bank currently only knows two states: full access or no access. We would like to set permissions per rank and per bank tab, e.g. members can deposit everywhere but only withdraw from the consumables tab.\n\nA daily withdraw limit per rank would also help a lot.",
            'history'  => [['2026-09-28', 'open']],
            'response' => null,
        ],
        4 => [
            'date' => '2026-09-25 09:15', 'state' => 'done', 'type' => 'Website', 'author' => 1005, 'up' => 43, 'down' => 2,
            'title' => 'Show item rarity colors in the database',
            'text'  => "In the item database all names are white. Using the rarity colors like in the game would make it much easier to scan the lists.",
            'history'  => [['2026-09-25', 'open'], ['2026-09-26', 'planned'], ['2026-09-29', 'done']],
            'response' => ['author' => 'Spl1nes', 'date' => '2026-09-29 19:02', 'text' => "Done - the database, auction house and character equipment now use the rarity colors."],
        ],
        5 => [
            'date' => '2026-09-21 22:37', 'state' => 'open', 'type' => 'Balance', 'author' => 1003, 'up' => 38, 'down' => 21,
            'title' => 'Berserker bleed damage too strong in 2v2',
            'text'  => "The Berserker bleed stacks too fast in 2v2. With two Berserkers you can stack it to 10 within 6 seconds and healers can't keep up, even with the cleanse on cooldown.\n\nMaybe cap the stacks in arenas or reduce the duration.",
            'history'  => [['2026-09-21', 'open']],
            'response' => null,
        ],
        6 => [
            'date' => '2026-09-18 17:44', 'state' => 'closed', 'type' => 'Suggestion', 'author' => 'Kaelith', 'up' => 52, 'down' => 23,
            'title' => 'Flying mounts in the open world',
            'text'  => "Flying mounts would make traveling the big open world faster and more fun.",
            'history'  => [['2026-09-18', 'open'], ['2026-09-22', 'closed']],
            'response' => ['author' => 'Spl1nes', 'date' => '2026-09-22 11:30', 'text' => "We decided against flying mounts. The world, world dungeons and open world PvP are built around ground travel. Faster ground mounts and more teleports are planned instead."],
        ],
        7 => [
            'date' => '2026-09-12 08:02', 'state' => 'done', 'type' => 'Bug', 'author' => 1002, 'up' => 19, 'down' => 1,
            'title' => 'Auction house sorting by price is reversed',
            'text'  => "Sorting by price ascending shows the most expensive items first and the other way around.",
            'history'  => [['2026-09-12', 'open'], ['2026-09-13', 'done']],
            'response' => ['author' => 'Morgwen', 'date' => '2026-09-13 16:20', 'text' => "Fixed with the last patch, thanks!"],
        ],
        8 => [
            'date' => '2026-09-08 20:11', 'state' => 'open', 'type' => 'Other', 'author' => 'Halys', 'up' => 12, 'down' => 6,
            'title' => 'Season character transfer after the season ends',
            'text'  => "What happens with the seasonal characters at the end of a season? It would be great if they could be transferred to the normal servers incl. their items.",
            'history'  => [['2026-09-08', 'open']],
            'response' => null,
        ],
    ];

    /**
     * Comments per feedback: author, date, text, up, down
     */
    private const COMMENTS = [
        1 => [
            [1001, '2026-10-02 19:02', 'Yes please! Also allow dyes on top of it.', 24, 1],
            ['Kaelith', '2026-10-02 20:45', 'Should be free to change, otherwise people will just not use it.', 17, 5],
            [1005, '2026-10-03 09:13', 'As long as PvP still shows the class silhouette clearly I am fine with it.', 11, 0],
            ['Spl1nes', '2026-10-05 10:14', 'Changing the look will cost a small amount of gold, that is a gold sink we need anyway.', 9, 3],
        ],
        2 => [
            [1001, '2026-09-30 21:30', 'Same with mounts in the Sunken Crypt.', 8, 0],
            [1003, '2026-10-01 08:55', 'Happens on ladders too.', 3, 0],
        ],
        3 => [
            [1002, '2026-09-28 14:10', 'A log of who took what would be great as well.', 15, 0],
        ],
        5 => [
            [1004, '2026-09-21 23:02', 'As a Druid I agree, it is impossible to outheal.', 12, 2],
            [1001, '2026-09-22 07:40', 'It is fine in 3v3, so only change it for 2v2 please.', 9, 4],
            ['Halys', '2026-09-22 12:15', 'Just play a Paladin and cleanse it.', 2, 14],
        ],
        6 => [
            ['Halys', '2026-09-18 18:00', 'Would ruin the world PvP.', 21, 4],
            [1004, '2026-09-23 10:20', 'Understandable decision, faster mounts sound good.', 7, 0],
        ],
        7 => [
            [1005, '2026-09-13 17:01', 'Works again, thanks for the quick fix.', 4, 0],
        ],
    ];

    /**
     * All feedback entries (newest first)
     *
     * @return array<int, array>
     */
    public static function all(array $samples) : array
    {
        $list = [];
        foreach (self::FEEDBACK as $id => $feedback) {
            $list[$id] = self::build($id, $feedback, $samples);
        }

        return $list;
    }

    /**
     * Single feedback entry incl. comments
     */
    public static function get(int $id, array $samples) : ?array
    {
        if (!isset(self::FEEDBACK[$id])) {
            return null;
        }

        $feedback             = self::build($id, self::FEEDBACK[$id], $samples);
        $feedback['comments'] = [];

        foreach (self::COMMENTS[$id] ?? [] as $i => $comment) {
            $feedback['comments'][] = [
                'id'     => $i + 1,
                'author' => self::author($comment[0], $samples),
                'date'   => new \DateTime($comment[1]),
                'text'   => $comment[2],
                'up'     => $comment[3],
                'down'   => $comment[4],
                'score'  => $comment[3] - $comment[4],
            ];
        }

        return $feedback;
    }

    /**
     * Filter and sort the feedback list
     *
     * @param string $query Search text
     * @param string $type  Type filter
     * @param string $state State filter (key of STATES)
     * @param string $sort  top|new|comments
     */
    public static function filter(array $list, string $query, string $type, string $state, string $sort) : array
    {
        $list = \array_filter($list, function ($f) use ($query, $type, $state) {
            return ($query === '' || \stripos($f['title'] . ' ' . $f['text'], $query) !== false)
                && ($type === '' || $f['type'] === $type)
                && ($state === '' || $f['state'] === $state);
        });

        \uasort($list, match ($sort) {
            'new'      => fn ($a, $b) => $b['date'] <=> $a['date'],
            'comments' => fn ($a, $b) => $b['comment_count'] <=> $a['comment_count'],
            default    => fn ($a, $b) => $b['score'] <=> $a['score'],
        });

        return $list;
    }

    private static function build(int $id, array $feedback, array $samples) : array
    {
        $comments = self::COMMENTS[$id] ?? [];
        $last     = \end($comments);

        return [
            'id'            => $id,
            'date'          => new \DateTime($feedback['date']),
            'state'         => $feedback['state'],
            'state_title'   => self::STATES[$feedback['state']],
            'type'          => $feedback['type'],
            'title'         => $feedback['title'],
            'text'          => $feedback['text'],
            'author'        => self::author($feedback['author'], $samples),
            'up'            => $feedback['up'],
            'down'          => $feedback['down'],
            'score'         => $feedback['up'] - $feedback['down'],
            'comment_count' => \count($comments),
            'last_activity' => new \DateTime($last === false ? $feedback['date'] : \max($last[1], $feedback['date'])),
            'history'       => \array_map(fn ($h) => ['date' => $h[0], 'state' => $h[1], 'title' => self::STATES[$h[1]]], $feedback['history']),
            'response'      => $feedback['response'] === null ? null : [
                'author' => self::author($feedback['response']['author'], $samples),
                'date'   => new \DateTime($feedback['response']['date']),
                'text'   => $feedback['response']['text'],
            ],
        ];
    }

    /**
     * Author info: name, link to the player profile (if any), developer flag
     */
    private static function author(int|string $author, array $samples) : array
    {
        if (\is_int($author) && isset($samples['players'][$author])) {
            return ['name' => $samples['players'][$author]['name'], 'link' => '/player/profile/' . $author, 'developer' => false];
        }

        return ['name' => (string) $author, 'link' => '', 'developer' => \in_array($author, self::DEVELOPERS, true)];
    }
}
