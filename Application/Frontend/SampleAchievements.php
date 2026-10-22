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
 * Sample achievements of an account.
 *
 * Every achievement has up to five tiers. Each reached tier grants achievement points.
 * The data is generated deterministically from the player id.
 *
 * @todo Replace with achievement_type / account_achievement once the game servers write them
 */
final class SampleAchievements
{
    /**
     * Points per tier
     */
    public const TIER_POINTS = [10, 20, 30, 40, 50];

    /**
     * Achievement catalog: name, category, description, unit and the five tier thresholds
     */
    private const CATALOG = [
        ['No lifer',          'Progression', 'Reach the maximum level with different classes (softcore)', 'classes',  [1, 3, 6, 9, 12]],
        ['I fear no death',   'Progression', 'Reach the maximum level with different classes (hardcore)', 'classes',  [1, 3, 6, 9, 12]],
        ['Storyteller',       'Progression', 'Complete chapters of the main story',                       'chapters', [1, 3, 5, 8, 12]],
        ['The Culling',       'Combat',      'Kill monsters',                                             'kills',    [100, 1000, 5000, 10000, 50000]],
        ['Slayer',            'Combat',      'Kill different players in the open world',                  'players',  [10, 50, 100, 250, 500]],
        ['Giant Killer',      'Combat',      'Defeat world bosses',                                       'kills',    [1, 5, 15, 30, 60]],
        ['Raider',            'Combat',      'Defeat raid bosses',                                        'kills',    [1, 10, 25, 50, 100]],
        ['Delver',            'Exploration', 'Complete different dungeons',                               'dungeons', [1, 3, 5, 8, 12]],
        ['Cartographer',      'Exploration', 'Discover locations',                                        'places',   [10, 50, 100, 200, 400]],
        ['Deep Diver',        'Exploration', 'Reach the bottom of underwater caves',                      'caves',    [1, 3, 6, 10, 15]],
        ['Gold Digger',       'Economy',     'Acquire gold',                                              'gold',     [1000, 10000, 100000, 500000, 1000000]],
        ['Trader',            'Economy',     'Sell items in the auction house',                           'sales',    [10, 100, 500, 1000, 5000]],
        ['Master Crafter',    'Economy',     'Craft items',                                               'items',    [50, 250, 1000, 2500, 10000]],
        ['Loot Goblin',       'Collection',  'Loot different item types',                                 'types',    [10, 50, 100, 250, 500]],
        ['Full Bling',        'Collection',  'Highest rarity in all equipment slots',                     'slots',    [2, 5, 8, 11, 14]],
        ['Beast Friend',      'Collection',  'Tame different pets',                                       'pets',     [1, 5, 10, 25, 50]],
        ['Gladiator',         'PvP',         'Win arena matches',                                         'wins',     [10, 50, 100, 250, 500]],
        ['Flag Bearer',       'PvP',         'Capture flags',                                             'flags',    [5, 25, 100, 250, 500]],
    ];

    /**
     * Achievements of a player incl. progress and unlock dates of the tiers
     *
     * @param int    $playerId Player id
     * @param string $since    Account creation date (Y-m-d)
     *
     * @return array<int, array{name:string, category:string, description:string, unit:string, tiers:int[], progress:int, reached:int, points:int, unlocked:array<int, \DateTime>}>
     */
    public static function forPlayer(int $playerId, string $since) : array
    {
        \mt_srand(\crc32('achievements-' . $playerId));

        $start = (new \DateTime($since))->getTimestamp();
        $end   = (new \DateTime('2026-10-08 22:00:00'))->getTimestamp();
        $span  = \max(1, $end - $start);

        // active players reach more tiers
        $activity = \max(0.2, 1 - ($playerId % 5) * 0.15);

        $achievements = [];
        foreach (self::CATALOG as $i => $def) {
            $tiers = $def[4];

            // progress somewhere between 0 and beyond the last tier
            $position = \min(5.2, \mt_rand(0, 520) / 100 * $activity + \mt_rand(0, 100) / 100);
            $reached  = (int) \floor(\min(5, $position));

            if ($reached >= 5) {
                $progress = $tiers[4];
            } else {
                $lower    = $reached === 0 ? 0 : $tiers[$reached - 1];
                $progress = (int) ($lower + ($tiers[$reached] - $lower) * ($position - $reached));
            }

            // unlock dates are spread over the account lifetime, higher tiers later
            $unlocked = [];
            $cursor   = $start + (int) ($span * \mt_rand(0, 25) / 100);
            for ($t = 0; $t < $reached; ++$t) {
                $cursor       = \min($end, $cursor + (int) ($span * \mt_rand(3, 18) / 100));
                $unlocked[$t] = (new \DateTime())->setTimestamp($cursor);
            }

            $achievements[] = [
                'name'        => $def[0],
                'category'    => $def[1],
                'description' => $def[2],
                'unit'        => $def[3],
                'tiers'       => $tiers,
                'progress'    => $progress,
                'reached'     => $reached,
                'points'      => \array_sum(\array_slice(self::TIER_POINTS, 0, $reached)),
                'unlocked'    => $unlocked,
            ];
        }

        return $achievements;
    }

    /**
     * Achievement point history (cumulative), oldest first
     *
     * @param array $achievements Result of forPlayer()
     *
     * @return array<int, array{date:\DateTime, points:int, title:string}>
     */
    public static function pointHistory(array $achievements) : array
    {
        $unlocks = [];
        foreach ($achievements as $achievement) {
            foreach ($achievement['unlocked'] as $tier => $date) {
                $unlocks[] = [
                    'date'   => $date,
                    'points' => self::TIER_POINTS[$tier],
                    'title'  => $achievement['name'] . ' ' . self::romanTier($tier + 1),
                ];
            }
        }

        \usort($unlocks, fn ($a, $b) => $a['date'] <=> $b['date']);

        $sum = 0;
        foreach ($unlocks as $i => $unlock) {
            $sum                    += $unlock['points'];
            $unlocks[$i]['total']    = $sum;
        }

        return $unlocks;
    }

    public static function romanTier(int $tier) : string
    {
        return ['I', 'II', 'III', 'IV', 'V'][$tier - 1] ?? (string) $tier;
    }
}
