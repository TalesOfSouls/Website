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

use Models\CharacterMapper;
use Models\Season;
use Models\SeasonBossKillMapper;
use Models\SeasonMapper;
use Models\SeasonProgressMapper;

/**
 * Seasonal results of an account
 *
 * Every season has a world dungeon that can only be entered with a new level 1 character.
 * At the end of the world dungeon wait one or more main bosses. Seasons may also have a PvP ladder
 * and a seasonal open world event.
 *
 * @package Application\Frontend\Provider
 * @since   1.0.0
 */
final class SeasonData extends DataAbstract
{
    /**
     * Seasonal level cap
     *
     * @var int
     * @since 1.0.0
     */
    public const MAX_LEVEL = 60;

    /**
     * PvP ladder divisions (lowest first)
     *
     * @var string[]
     * @since 1.0.0
     */
    public const DIVISIONS = ['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Champion'];

    /**
     * Seasons that already started (newest first)
     *
     * @return Season[]
     *
     * @since 1.0.0
     */
    public function started() : array
    {
        $now     = new \DateTime();
        $seasons = [];

        foreach (SeasonMapper::getAll()->with('bosses')->sort('start', 'DESC')->executeGetArray() as $season) {
            if ($season->start <= $now) {
                $seasons[$season->id] = $season;
            }
        }

        return $seasons;
    }

    /**
     * Division of a ladder rating
     *
     * @param int $rating Rating
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function division(int $rating) : string
    {
        return self::DIVISIONS[(int) \min(\count(self::DIVISIONS) - 1, \max(0, \floor(($rating - 900) / 250)))];
    }

    /**
     * Seasonal results of an account (newest season first)
     *
     * A season the account didn't take part in has 'played' => false.
     *
     * @param int $account Account id
     *
     * @return array<int, array>
     *
     * @since 1.0.0
     */
    public function forAccount(int $account) : array
    {
        $progress = [];
        foreach (SeasonProgressMapper::getAll()->where('account', $account)->executeGetArray() as $p) {
            $progress[$p->season] = $p;
        }

        $kills = [];
        foreach (SeasonBossKillMapper::getAll()->where('account', $account)->executeGetArray() as $kill) {
            $kills[$kill->boss] = $kill;
        }

        $characters = [];
        foreach (CharacterMapper::getAll()->with('class')->where('account', $account)->where('season', null, '!=')->executeGetArray() as $character) {
            $characters[$character->season][] = [
                'id'      => $character->id,
                'name'    => $character->name,
                'class'   => $character->class->name,
                'level'   => $character->getLevel(),
                'created' => $character->created ?? new \DateTime(),
                'died'    => $character->dead,
            ];
        }

        $results = [];
        foreach ($this->started() as $id => $season) {
            $base = [
                'season'         => $id,
                'name'           => $season->name,
                'start'          => $season->start,
                'end'            => $season->end,
                'dungeon'        => $season->dungeon,
                'zones'          => $season->zones,
                'elites'         => $season->elites,
                'bosses'         => \array_map(fn ($b) => $b->name, \array_values($season->bosses)),
                'ladder_enabled' => $season->ladder,
                'event'          => $season->event,
                'current'        => $season->isCurrent(),
            ];

            $p = $progress[$id] ?? null;
            if ($p === null) {
                $results[$id] = $base + ['played' => false];
                continue;
            }

            $bosses = \array_values($season->bosses);
            \usort($bosses, fn ($a, $b) => $a->order <=> $b->order);

            $bossList = [];
            foreach ($bosses as $boss) {
                $kill       = $kills[$boss->id] ?? null;
                $bossList[] = [
                    'name'     => $boss->name,
                    'killed'   => $kill !== null && $kill->killed,
                    'date'     => $kill?->datetime,
                    'attempts' => $kill->attempts ?? 0,
                ];
            }

            $bossesKilled = \count(\array_filter($bossList, fn ($b) => $b['killed']));

            $ladder = null;
            if ($p->ladderRating !== null) {
                $ladder = [
                    'rating'   => $p->ladderRating,
                    'division' => self::division($p->ladderRating),
                    'rank'     => (int) $p->ladderRank,
                    'players'  => (int) $p->ladderPlayers,
                    'wins'     => (int) $p->ladderWins,
                    'losses'   => (int) $p->ladderLosses,
                    'final'    => !$season->isCurrent(),
                ];
            }

            $chars = $characters[$id] ?? [];
            \usort($chars, fn ($a, $b) => $a['created'] <=> $b['created']);

            $results[$id] = $base + [
                'played'        => true,
                'characters'    => $chars,
                'zones_done'    => $p->zones,
                'elites_done'   => $p->elites,
                'boss_list'     => $bossList,
                'bosses_killed' => $bossesKilled,
                // progression: zones 30%, elites 30%, main bosses 40% (open world events are not part of it)
                'progress'      => (int) \round(
                    30 * $p->zones / \max(1, $season->zones)
                    + 30 * $p->elites / \max(1, $season->elites)
                    + 40 * $bossesKilled / \max(1, \count($bossList))
                ),
                'ladder'        => $ladder,
                'events'        => $p->events,
            ];
        }

        return $results;
    }
}
