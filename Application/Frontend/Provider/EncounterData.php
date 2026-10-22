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

use Models\Encounter;
use Models\EncounterMapper;
use Models\EncounterMember;
use Models\EncounterType;

/**
 * Dungeon runs, raids, world boss fights and PvP matches of an account
 *
 * @package Application\Frontend\Provider
 * @since   1.0.0
 */
final class EncounterData extends DataAbstract
{
    /**
     * Encounter types (url name => title)
     *
     * @var array<string, string>
     * @since 1.0.0
     */
    public const TYPES = [
        EncounterType::DUNGEON   => 'Dungeons',
        EncounterType::RAID      => 'Raids',
        EncounterType::WORLDBOSS => 'World bosses',
        EncounterType::PVP       => 'PvP',
    ];

    /**
     * Encounters per page
     *
     * @var int
     * @since 1.0.0
     */
    public const PER_PAGE = 30;

    /**
     * Encounters of an account (newest first)
     *
     * @param int    $account Account id
     * @param string $type    Encounter type
     * @param int    $page    Page (1 = first)
     *
     * @return array{encounters:array, count:int}
     *
     * @since 1.0.0
     */
    public function forAccount(int $account, string $type, int $page = 1) : array
    {
        if (!isset(self::TYPES[$type])) {
            return ['encounters' => [], 'count' => 0];
        }

        $from = 'FROM encounter_member m
            JOIN "character" c ON c.character_id = m.encounter_member_character
            JOIN encounter e ON e.encounter_id = m.encounter_member_encounter
            JOIN instance i ON i.instance_id = e.encounter_instance
            WHERE c.character_account = ? AND e.encounter_type = ?';

        $count = (int) $this->value('SELECT COUNT(*) ' . $from, [$account, $type]);

        $rows = $this->rows(
            'SELECT e.*, i.instance_name, i.instance_location, m.*, c.character_id, c.character_name,
                (SELECT COUNT(*) FROM encounter_member o WHERE o.encounter_member_encounter = e.encounter_id) AS players,
                (SELECT COUNT(*) FROM encounter_boss b WHERE b.encounter_boss_encounter = e.encounter_id) AS bosses_total,
                (SELECT COUNT(*) FROM encounter_boss b WHERE b.encounter_boss_encounter = e.encounter_id AND b.encounter_boss_killed = 1) AS bosses_killed,
                (SELECT COUNT(*) FROM encounter_member o WHERE o.encounter_member_encounter = e.encounter_id AND o.encounter_member_damage > m.encounter_member_damage) + 1 AS damage_rank
            ' . $from . '
            ORDER BY e.encounter_start DESC
            LIMIT ' . self::PER_PAGE . ' OFFSET ' . (($page - 1) * self::PER_PAGE),
            [$account, $type]
        );

        $encounters = [];
        foreach ($rows as $row) {
            $team    = (int) $row['encounter_member_team'];
            $isPvp   = $type === EncounterType::PVP;
            $success = $isPvp ? (int) $row['encounter_winner'] === $team : (bool) $row['encounter_success'];

            $encounters[] = [
                'id'            => (int) $row['encounter_id'],
                'type'          => $type,
                'title'         => $isPvp ? $row['encounter_difficulty'] : $row['instance_name'],
                'location'      => $isPvp ? $row['instance_name'] : $row['instance_location'],
                'difficulty'    => $row['encounter_difficulty'],
                'date'          => new \DateTime($row['encounter_start']),
                'duration'      => (int) $row['encounter_duration'],
                'success'       => $success,
                'killed'        => (int) $row['bosses_killed'],
                'total'         => (int) $row['bosses_total'],
                'players'       => (int) $row['players'],
                'rank'          => (int) $row['damage_rank'],
                'score'         => [
                    'own'   => (int) ($team === 1 ? $row['encounter_score1'] : $row['encounter_score2']),
                    'enemy' => (int) ($team === 1 ? $row['encounter_score2'] : $row['encounter_score1']),
                ],
                'rating_before' => (int) $row['encounter_member_rating_before'],
                'rating_change' => (int) $row['encounter_member_rating_change'],
                'self'          => [
                    'id'      => (int) $row['character_id'],
                    'name'    => $row['character_name'],
                    'damage'  => (int) $row['encounter_member_damage'],
                    'healing' => (int) $row['encounter_member_healing'],
                    'deaths'  => (int) $row['encounter_member_deaths'],
                    'kills'   => (int) $row['encounter_member_kills'],
                    'assists' => (int) $row['encounter_member_assists'],
                ],
            ];
        }

        return ['encounters' => $encounters, 'count' => $count];
    }

    /**
     * Current PvP rating of an account (rating of the last match)
     *
     * @param int $account Account id
     *
     * @return null|int
     *
     * @since 1.0.0
     */
    public function pvpRating(int $account) : ?int
    {
        $value = $this->value(
            'SELECT m.encounter_member_rating_before + m.encounter_member_rating_change
            FROM encounter_member m
            JOIN "character" c ON c.character_id = m.encounter_member_character
            JOIN encounter e ON e.encounter_id = m.encounter_member_encounter
            WHERE c.character_account = ? AND e.encounter_type = \'pvp\'
            ORDER BY e.encounter_start DESC LIMIT 1',
            [$account]
        );

        return $value === null ? null : (int) $value;
    }

    /**
     * PvP summary of an account
     *
     * @param int $account Account id
     *
     * @return array{matches:int, wins:int, kills:int, deaths:int}
     *
     * @since 1.0.0
     */
    public function pvpSummary(int $account) : array
    {
        $row = $this->rows(
            'SELECT COUNT(*) AS matches,
                SUM(CASE WHEN e.encounter_winner = m.encounter_member_team THEN 1 ELSE 0 END) AS wins,
                COALESCE(SUM(m.encounter_member_kills), 0) AS kills,
                COALESCE(SUM(m.encounter_member_deaths), 0) AS deaths
            FROM encounter_member m
            JOIN "character" c ON c.character_id = m.encounter_member_character
            JOIN encounter e ON e.encounter_id = m.encounter_member_encounter
            WHERE c.character_account = ? AND e.encounter_type = \'pvp\'',
            [$account]
        )[0] ?? [];

        return [
            'matches' => (int) ($row['matches'] ?? 0),
            'wins'    => (int) ($row['wins'] ?? 0),
            'kills'   => (int) ($row['kills'] ?? 0),
            'deaths'  => (int) ($row['deaths'] ?? 0),
        ];
    }

    /**
     * Single encounter seen from the perspective of an account
     *
     * @param int    $account Account id
     * @param string $type    Encounter type
     * @param int    $id      Encounter id
     *
     * @return null|array
     *
     * @since 1.0.0
     */
    public function get(int $account, string $type, int $id) : ?array
    {
        /** @var Encounter $encounter */
        $encounter = EncounterMapper::get()
            ->with('instance')
            ->with('members')
            ->with('members/character')
            ->with('members/character/class')
            ->with('members/skills')
            ->with('members/skills/skill')
            ->with('bosses')
            ->with('bosses/boss')
            ->with('bosses/attempts')
            ->with('bosses/stats')
            ->with('scores')
            ->where('id', $id)
            ->execute();

        if ($encounter->id === 0 || $encounter->type !== $type) {
            return null;
        }

        // the member of the account (highest damage if several characters of the account took part)
        $self = null;
        foreach ($encounter->members as $member) {
            if ($member->character->account->id === $account
                && ($self === null || $member->damage > $self->damage)
            ) {
                $self = $member;
            }
        }

        if ($self === null) {
            return null;
        }

        $members = \array_values($encounter->members);
        \usort($members, fn ($a, $b) => $a->id <=> $b->id);

        $byMemberId = [];
        $all        = [];
        foreach ($members as $member) {
            $all[]                     = $this->memberToArray($member, $encounter->duration);
            $byMemberId[$member->id]   = \count($all) - 1;
        }

        $selfArray = $all[$byMemberId[$self->id]];

        $byDamage = $all;
        \usort($byDamage, fn ($a, $b) => $b['damage'] <=> $a['damage']);
        $rank = 1 + (int) \array_search($self->id, \array_column($byDamage, 'member_id'));

        $base = [
            'id'         => $encounter->id,
            'type'       => $encounter->type,
            'date'       => $encounter->start,
            'duration'   => $encounter->duration,
            'difficulty' => $encounter->difficulty,
            'self'       => $selfArray,
            'rank'       => $rank,
        ];

        if ($type === EncounterType::PVP) {
            $team  = $self->team;
            $teams = ['own' => [], 'enemy' => []];
            foreach ($all as $member) {
                $teams[$member['team'] === $team ? 'own' : 'enemy'][] = $member;
            }

            // the own character first
            \usort($teams['own'], fn ($a, $b) => ($b['member_id'] === $self->id) <=> ($a['member_id'] === $self->id));

            $timeline = [];
            $scores   = \array_values($encounter->scores);
            \usort($scores, fn ($a, $b) => $a->time <=> $b->time);
            foreach ($scores as $score) {
                $timeline[] = [
                    'time'  => $score->time,
                    'own'   => $team === 1 ? $score->score1 : $score->score2,
                    'enemy' => $team === 1 ? $score->score2 : $score->score1,
                ];
            }

            $mode = $this->rows(
                'SELECT p.* FROM pvp_mode p JOIN instance i ON i.instance_pvp_mode = p.pvp_mode_id WHERE i.instance_id = ?',
                [$encounter->instance->id]
            )[0] ?? [];

            return $base + [
                'title'         => $encounter->difficulty,
                'location'      => $encounter->instance->name,
                'unit'          => $mode['pvp_mode_unit'] ?? '',
                'target'        => (int) ($mode['pvp_mode_target'] ?? 0),
                'success'       => $encounter->winner === $team,
                'score'         => [
                    'own'   => (int) ($team === 1 ? $encounter->score1 : $encounter->score2),
                    'enemy' => (int) ($team === 1 ? $encounter->score2 : $encounter->score1),
                ],
                'timeline'      => $timeline,
                'teams'         => $teams,
                'group'         => $teams['own'],
                'rating_before' => (int) $self->ratingBefore,
                'rating_change' => (int) $self->ratingChange,
            ];
        }

        // PvE: own character first, the boss statistics are indexed like the group
        \usort($all, fn ($a, $b) => ($b['member_id'] === $self->id) <=> ($a['member_id'] === $self->id));
        $groupIndex = [];
        foreach ($all as $i => $member) {
            $groupIndex[$member['member_id']] = $i;
        }

        $bosses = \array_values($encounter->bosses);
        \usort($bosses, fn ($a, $b) => $a->boss->order <=> $b->boss->order);

        $bossList = [];
        foreach ($bosses as $boss) {
            $attempts = \array_values($boss->attempts);
            \usort($attempts, fn ($a, $b) => $a->no <=> $b->no);

            $entry = [
                'name'     => $boss->boss->name,
                'killed'   => $boss->killed,
                'attempts' => \array_map(fn ($a) => [
                    'no'       => $a->no,
                    'duration' => $a->duration,
                    'hp_left'  => $a->hpLeft,
                    'kill'     => $a->kill,
                ], $attempts),
            ];

            if (!empty($boss->stats)) {
                $entry += ['damage' => [], 'healing' => [], 'received' => [], 'taken' => []];

                foreach ($boss->stats as $stat) {
                    if (!isset($groupIndex[$stat->member])) {
                        continue;
                    }

                    $i = $groupIndex[$stat->member];
                    $entry['damage'][$i]   = $stat->damage;
                    $entry['healing'][$i]  = $stat->healing;
                    $entry['received'][$i] = $stat->received;
                    $entry['taken'][$i]    = $stat->taken;
                }
            }

            $bossList[] = $entry;
        }

        return $base + [
            'title'    => $encounter->instance->name,
            'location' => $encounter->instance->location,
            'success'  => $encounter->success,
            'killed'   => \count(\array_filter($bosses, fn ($b) => $b->killed)),
            'total'    => \count($bosses),
            'bosses'   => $bossList,
            'group'    => $all,
        ];
    }

    /**
     * Member as array for the templates
     *
     * @param EncounterMember $member   Member
     * @param int             $duration Encounter duration
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function memberToArray(EncounterMember $member, int $duration) : array
    {
        $skills = [];
        foreach ($member->skills as $skill) {
            $skills[] = [
                'name'     => $skill->skill->name,
                'cd'       => (int) $skill->skill->cooldown,
                'kind'     => $skill->skill->kind,
                'target'   => $skill->skill->target,
                'possible' => $skill->possible,
                'casts'    => $skill->casts,
                'amount'   => $skill->amount,
            ];
        }

        // cooldown order: filler first, then by cooldown
        \usort($skills, fn ($a, $b) => $a['cd'] <=> $b['cd']);

        return [
            'member_id'        => $member->id,
            'id'               => $member->character->id,
            'name'             => $member->character->name,
            'class'            => $member->character->class->name,
            'team'             => $member->team,
            'role'             => $member->role,
            'damage'           => $member->damage,
            'damage_single'    => $member->damageSingle,
            'damage_aoe'       => $member->damageAoe,
            'healing'          => $member->healing,
            'healing_single'   => $member->healingSingle,
            'healing_aoe'      => $member->healingAoe,
            'healing_received' => $member->healingReceived,
            'damage_taken'     => $member->damageTaken,
            'dps'              => (int) \round($member->damage / \max(1, $duration)),
            'hps'              => (int) \round($member->healing / \max(1, $duration)),
            'deaths'           => $member->deaths,
            'kills'            => $member->kills,
            'assists'          => $member->assists,
            'objectives'       => $member->objectives,
            'casts'            => $member->casts,
            'apm'              => $member->apm,
            'cooldown_usage'   => $member->cooldownUsage,
            'active'           => $member->active,
            'skills'           => $skills,
        ];
    }
}
