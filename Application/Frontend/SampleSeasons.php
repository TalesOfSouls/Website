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
 * Sample seasons and the seasonal results of an account.
 *
 * Every season has a world dungeon (a smaller open world inside the open world) that can only be
 * entered with a new level 1 character. At the end of the world dungeon wait one or more main bosses.
 * Seasons may also have a PvP ladder and seasonal open world events.
 *
 * Generated deterministically from the player id.
 *
 * @todo Replace with season / season_character / season_progress tables once the game servers write them
 */
final class SampleSeasons
{
    /**
     * Seasonal level cap
     */
    public const MAX_LEVEL = 60;

    /**
     * PvP ladder divisions (lowest first)
     */
    public const DIVISIONS = ['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Champion'];

    /**
     * Season catalog
     *
     * zones: areas of the world dungeon, elites: optional bosses inside the world dungeon,
     * bosses: main bosses at the end, event: seasonal open world event (not part of the progression)
     */
    public const SEASONS = [
        1 => ['name' => 'Season of Embers',  'start' => '2024-07-01', 'end' => '2024-10-31', 'dungeon' => 'The Cinder Vale',    'zones' => 6, 'elites' => 10, 'bosses' => ['Ignar, the Ash Tyrant'],                                  'ladder' => false, 'event' => 'Ember rifts closed'],
        2 => ['name' => 'Season of Tides',   'start' => '2024-11-01', 'end' => '2025-03-31', 'dungeon' => 'The Drowned Reach',  'zones' => 7, 'elites' => 12, 'bosses' => ['Thalassa', 'The Leviathan'],                             'ladder' => true,  'event' => 'Tidal shrines cleansed'],
        3 => ['name' => 'Season of Thorns',  'start' => '2025-04-01', 'end' => '2025-07-15', 'dungeon' => 'The Briar Maze',     'zones' => 6, 'elites' => 11, 'bosses' => ['The Thorn Queen'],                                        'ladder' => true,  'event' => 'Overgrown camps burned'],
        4 => ['name' => 'Season of Frost',   'start' => '2025-07-16', 'end' => '2025-12-31', 'dungeon' => 'Rimeholt',           'zones' => 9, 'elites' => 15, 'bosses' => ['Jarl Vinterkald', 'Skadi the Huntress', 'Hrimgar the Pale'], 'ladder' => true,  'event' => 'Frozen caravans rescued'],
        5 => ['name' => 'Season of Shadows', 'start' => '2026-01-01', 'end' => '2026-04-30', 'dungeon' => 'The Umbral Hollow',  'zones' => 7, 'elites' => 12, 'bosses' => ['Nyx', 'The Faceless One'],                               'ladder' => true,  'event' => 'Shadow gates sealed'],
        6 => ['name' => 'Season of the Styx', 'start' => '2026-05-01', 'end' => '2026-08-31', 'dungeon' => 'The Styx Depths',   'zones' => 8, 'elites' => 14, 'bosses' => ['Charon'],                                                 'ladder' => true,  'event' => 'Lost souls ferried'],
        7 => ['name' => 'Season of Storms',  'start' => '2026-09-01', 'end' => '2027-01-31', 'dungeon' => 'The Thunder Steppe', 'zones' => 8, 'elites' => 13, 'bosses' => ['Voltara', 'Storm King Arkon'],                          'ladder' => true,  'event' => 'Lightning totems captured'],
    ];

    private const CLASSES = ['Paladin', 'Knight', 'Mage', 'Priest', 'Berserker', 'Druid', 'Hunter', 'Necromancer', 'Rogue'];

    private const NAME_PARTS = [
        ['Ash', 'Bryn', 'Cael', 'Dorn', 'Eld', 'Fae', 'Gar', 'Hal', 'Isen', 'Jor', 'Kae', 'Lys', 'Mor', 'Nym', 'Os', 'Rav', 'Ser', 'Tor', 'Vel', 'Wyn'],
        ['a', 'en', 'ric', 'wyn', 'ia', 'or', 'eth', 'ys', 'mir', 'dra', 'lin', 'oc'],
    ];

    /**
     * Date "today" of the sample data
     */
    public const NOW = '2026-10-08 22:00:00';

    /**
     * Season status: ended, current
     */
    public static function isCurrent(int $season) : bool
    {
        $now = new \DateTime(self::NOW);

        return new \DateTime(self::SEASONS[$season]['start']) <= $now
            && new \DateTime(self::SEASONS[$season]['end'] . ' 23:59:59') >= $now;
    }

    /**
     * Seasons that already started (newest first)
     *
     * @return array<int, array>
     */
    public static function started() : array
    {
        $now     = new \DateTime(self::NOW);
        $seasons = \array_filter(self::SEASONS, fn ($s) => new \DateTime($s['start']) <= $now);
        \krsort($seasons);

        return $seasons;
    }

    /**
     * Seasonal results of a player (newest season first)
     *
     * A season the player didn't take part in has 'played' => false.
     *
     * @param int    $playerId Player id
     * @param string $since    Account creation date (Y-m-d)
     *
     * @return array<int, array>
     */
    public static function forPlayer(int $playerId, string $since) : array
    {
        $results  = [];
        $created  = new \DateTime($since);
        $activity = \max(0.25, 1 - ($playerId % 5) * 0.17);
        $now      = new \DateTime(self::NOW);

        foreach (self::started() as $id => $season) {
            \mt_srand(\crc32('season-' . $playerId . '-' . $id));

            $start = new \DateTime($season['start']);
            $end   = \min($now, new \DateTime($season['end'] . ' 23:59:59'));

            // seasons before the account existed or skipped seasons
            $played = new \DateTime($season['end']) >= $created && \mt_rand(0, 100) < 55 + $activity * 45;
            if (!$played) {
                $results[$id] = ['season' => $id, 'played' => false] + $season;
                continue;
            }

            $from = \max($start, $created);
            $span = \max(86400, $end->getTimestamp() - $from->getTimestamp());

            // ongoing seasons are less far progressed
            $elapsed = \min(1.0, ($now->getTimestamp() - $start->getTimestamp())
                / \max(1, (new \DateTime($season['end']))->getTimestamp() - $start->getTimestamp()));
            $skill   = \min(1.0, $activity * \mt_rand(60, 120) / 100) * (self::isCurrent($id) ? \min(1.0, $elapsed * 1.6) : 1.0);

            // seasonal characters (a new level 1 character is required to enter the world dungeon)
            $characters = [];
            $count      = \mt_rand(0, 100) < 25 ? 2 : 1;
            for ($c = 0; $c < $count; ++$c) {
                $level = $c === 0
                    ? (int) \max(5, \min(self::MAX_LEVEL, \round(self::MAX_LEVEL * \min(1.0, $skill * 1.25))))
                    : \mt_rand(8, 35);

                $characters[] = [
                    'name'    => self::name(),
                    'class'   => self::CLASSES[\mt_rand(0, \count(self::CLASSES) - 1)],
                    'level'   => $level,
                    'created' => (new \DateTime())->setTimestamp($from->getTimestamp() + (int) ($span * ($c === 0 ? \mt_rand(0, 6) : \mt_rand(20, 60)) / 100) + 3600 * \mt_rand(16, 22)),
                    'died'    => $c > 0 && \mt_rand(0, 1) === 1,
                ];
            }

            // world dungeon progress
            $zones  = (int) \min($season['zones'], \round($season['zones'] * \min(1.0, $skill * 1.3)));
            $elites = (int) \min($season['elites'], \round($season['elites'] * $skill * \mt_rand(70, 110) / 100));

            $bosses       = [];
            $reachedEnd   = $zones === $season['zones'] && $characters[0]['level'] >= self::MAX_LEVEL;
            $bossChance   = $skill;
            foreach ($season['bosses'] as $b => $bossName) {
                $attempts = 0;
                $killDate = null;

                if ($reachedEnd && ($b === 0 || $bosses[$b - 1]['killed'])) {
                    $attempts = \mt_rand(2, 25);
                    if (\mt_rand(0, 100) < $bossChance * 100) {
                        $killDate = (new \DateTime())->setTimestamp(
                            $from->getTimestamp() + (int) ($span * (0.55 + 0.4 * ($b + 1) / (\count($season['bosses']) + 1)))
                        );
                    }

                    $bossChance *= 0.8;
                }

                $bosses[] = [
                    'name'     => $bossName,
                    'killed'   => $killDate !== null,
                    'date'     => $killDate,
                    'attempts' => $attempts,
                ];
            }

            $bossesKilled = \count(\array_filter($bosses, fn ($b) => $b['killed']));

            // progression: zones 30%, elites 30%, main bosses 40% (open world events are not part of it)
            $progress = (int) \round(
                30 * $zones / $season['zones']
                + 30 * $elites / $season['elites']
                + 40 * $bossesKilled / \count($season['bosses'])
            );

            // PvP ladder (optional)
            $ladder = null;
            if ($season['ladder'] && \mt_rand(0, 100) < 70) {
                $share    = self::isCurrent($id) ? \max(0.1, $elapsed) : 1.0;
                $wins     = (int) \max(3, \mt_rand(5, 140) * $share);
                $losses   = (int) \max(3, \mt_rand(5, 110) * $share);
                $rating   = (int) \max(800, 1200 + ($wins - $losses) * \mt_rand(4, 9) + \mt_rand(-80, 120));
                $division = (int) \min(\count(self::DIVISIONS) - 1, \max(0, \floor(($rating - 900) / 250)));
                $ladder   = [
                    'rating'   => $rating,
                    'division' => self::DIVISIONS[$division],
                    'rank'     => \max(1, (int) (4000 - $rating * 1.6 + \mt_rand(0, 300))),
                    'players'  => \mt_rand(4200, 6800),
                    'wins'     => $wins,
                    'losses'   => $losses,
                    'final'    => !self::isCurrent($id),
                ];
            }

            $results[$id] = [
                'season'     => $id,
                'played'     => true,
                'characters' => $characters,
                'zones_done' => $zones,
                'elites_done' => $elites,
                'boss_list'  => $bosses,
                'bosses_killed' => $bossesKilled,
                'progress'   => $progress,
                'ladder'     => $ladder,
                'ladder_enabled' => $season['ladder'],
                'events'     => (int) (\mt_rand(5, 120) * $activity),
                'current'    => self::isCurrent($id),
            ] + $season;
        }

        return $results;
    }

    private static function name() : string
    {
        return self::NAME_PARTS[0][\mt_rand(0, \count(self::NAME_PARTS[0]) - 1)]
            . self::NAME_PARTS[1][\mt_rand(0, \count(self::NAME_PARTS[1]) - 1)];
    }
}
