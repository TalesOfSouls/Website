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

/**
 * Ladders (rankings)
 *
 * @package Application\Frontend\Provider
 * @since   1.0.0
 */
final class LadderData extends DataAbstract
{
    /**
     * Entries per ladder
     *
     * @var int
     * @since 1.0.0
     */
    public const LIMIT = 100;

    /**
     * Ladder types that are tracked (type => value column title)
     *
     * @var array<string, string>
     * @since 1.0.0
     */
    public const TYPES = [
        'Level'          => 'Level',
        'Achievement'    => 'Points',
        'Raid'           => 'Bosses killed',
        'Grouped Dungeon' => 'Dungeons cleared',
        'Any PvP'        => 'Rating',
        'DPS'            => 'Best DPS',
        'Healing'        => 'Best HPS',
    ];

    /**
     * Ladder
     *
     * @param array{type:string, player:string, server:string, class:string, mode:string, seasonal:bool} $filter Filter
     *
     * @return null|array<int, array{name:string, link:string, class:string, mode:string, server:string, value:string}> null if the ladder is not tracked
     *
     * @since 1.0.0
     */
    public function get(array $filter) : ?array
    {
        $type = $filter['type'];
        if (\str_starts_with($type, 'PvP')) {
            $type = 'Any PvP';
        }

        return match ($type) {
            'Level'           => $this->characters($filter, 'c.character_xp', 'character_xp DESC'),
            'Achievement'     => $this->players($filter),
            'Raid'            => $this->guilds($filter),
            'Grouped Dungeon' => $this->characters(
                $filter,
                '(SELECT COUNT(*) FROM encounter_member m JOIN encounter e ON e.encounter_id = m.encounter_member_encounter WHERE m.encounter_member_character = c.character_id AND e.encounter_type = \'dungeon\' AND e.encounter_success = 1)',
                'value DESC'
            ),
            'Any PvP' => $this->characters(
                $filter,
                '(SELECT m.encounter_member_rating_before + m.encounter_member_rating_change FROM encounter_member m JOIN encounter e ON e.encounter_id = m.encounter_member_encounter WHERE m.encounter_member_character = c.character_id AND e.encounter_type = \'pvp\' ORDER BY e.encounter_start DESC LIMIT 1)',
                'value DESC'
            ),
            'DPS' => $this->characters(
                $filter,
                '(SELECT MAX(CAST(m.encounter_member_damage AS REAL) / e.encounter_duration) FROM encounter_member m JOIN encounter e ON e.encounter_id = m.encounter_member_encounter WHERE m.encounter_member_character = c.character_id AND e.encounter_type != \'pvp\')',
                'value DESC'
            ),
            'Healing' => $this->characters(
                $filter,
                '(SELECT MAX(CAST(m.encounter_member_healing AS REAL) / e.encounter_duration) FROM encounter_member m JOIN encounter e ON e.encounter_id = m.encounter_member_encounter WHERE m.encounter_member_character = c.character_id AND e.encounter_type != \'pvp\')',
                'value DESC'
            ),
            default => null,
        };
    }

    /**
     * Character ladder
     *
     * @param array  $filter Filter
     * @param string $value  Sql expression of the ranked value
     * @param string $order  Order by
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function characters(array $filter, string $value, string $order) : array
    {
        $where  = ['c.character_dead = 0'];
        $params = [];

        if ($filter['seasonal']) {
            $where[]  = 'c.character_season = (SELECT season_id FROM season WHERE season_start <= ? AND season_end >= ? LIMIT 1)';
            $params[] = (new \DateTime())->format('Y-m-d H:i:s');
            $params[] = (new \DateTime())->format('Y-m-d H:i:s');
        } else {
            $where[] = 'c.character_season IS NULL';
        }

        if ($filter['mode'] !== '' && $filter['mode'] !== 'Overall') {
            $where[] = 'c.character_hardcore = ' . (\str_starts_with($filter['mode'], 'HC') || $filter['mode'] === 'Any HC' ? 1 : 0);
        }

        if ($filter['player'] !== '') {
            $where[]  = '(c.character_name LIKE ? OR a.account_name LIKE ?)';
            $params[] = '%' . $filter['player'] . '%';
            $params[] = '%' . $filter['player'] . '%';
        }

        if ($filter['server'] !== '') {
            $where[]  = 's.server_name LIKE ?';
            $params[] = '%' . $filter['server'] . '%';
        }

        if ($filter['class'] !== '') {
            $where[]  = 'cl.class_name = ?';
            $params[] = $filter['class'];
        }

        $rows = $this->rows(
            'SELECT * FROM (
                SELECT c.character_id, c.character_name, c.character_xp, c.character_hardcore, cl.class_name, s.server_name, ' . $value . ' AS value
                FROM "character" c
                LEFT JOIN account a ON a.account_id = c.character_account
                LEFT JOIN class cl ON cl.class_id = c.character_class
                LEFT JOIN server s ON s.server_id = c.character_server
                WHERE ' . \implode(' AND ', $where) . '
            ) WHERE value IS NOT NULL AND value > 0
            ORDER BY ' . $order . ', character_name
            LIMIT ' . self::LIMIT,
            $params
        );

        $ladder = [];
        foreach ($rows as $row) {
            $ladder[] = [
                'name'   => $row['character_name'],
                'link'   => '/player/character/profile/' . $row['character_id'],
                'class'  => $row['class_name'] ?? '-',
                'mode'   => $row['character_hardcore'] ? 'HC' : 'SC',
                'server' => $row['server_name'] ?? '-',
                'value'  => $order === 'character_xp DESC'
                    ? (string) \Models\PlayerLevel::levelFromXp((int) $row['character_xp'])
                    : \number_format((float) $row['value']),
            ];
        }

        return $ladder;
    }

    /**
     * Player ladder (achievement points)
     *
     * @param array $filter Filter
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function players(array $filter) : array
    {
        $params = [];
        $where  = 'a.account_status = 1';

        if ($filter['player'] !== '') {
            $where   .= ' AND a.account_name LIKE ?';
            $params[] = '%' . $filter['player'] . '%';
        }

        $rows = $this->rows(
            'SELECT a.account_id, a.account_name, SUM(t.account_achievement_tier_tier * 10) AS value
            FROM account a
            JOIN account_achievement_tier t ON t.account_achievement_tier_account = a.account_id
            WHERE ' . $where . '
            GROUP BY a.account_id
            ORDER BY value DESC, a.account_name
            LIMIT ' . self::LIMIT,
            $params
        );

        return \array_map(fn ($row) => [
            'name'   => $row['account_name'],
            'link'   => '/player/profile/' . $row['account_id'],
            'class'  => '-',
            'mode'   => '-',
            'server' => '-',
            'value'  => \number_format((int) $row['value']),
        ], $rows);
    }

    /**
     * Guild ladder (killed raid bosses)
     *
     * @param array $filter Filter
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function guilds(array $filter) : array
    {
        $params = [];
        $where  = 'g.guild_created IS NOT NULL';

        if ($filter['player'] !== '') {
            $where   .= ' AND g.guild_name LIKE ?';
            $params[] = '%' . $filter['player'] . '%';
        }

        if ($filter['server'] !== '') {
            $where   .= ' AND s.server_name LIKE ?';
            $params[] = '%' . $filter['server'] . '%';
        }

        $rows = $this->rows(
            'SELECT g.guild_id, g.guild_name, s.server_name, COUNT(DISTINCT b.encounter_boss_id) AS value
            FROM guild g
            LEFT JOIN server s ON s.server_id = g.guild_server
            LEFT JOIN "character" c ON c.character_guild = g.guild_id
            LEFT JOIN encounter_member m ON m.encounter_member_character = c.character_id
            LEFT JOIN encounter e ON e.encounter_id = m.encounter_member_encounter AND e.encounter_type = \'raid\'
            LEFT JOIN encounter_boss b ON b.encounter_boss_encounter = e.encounter_id AND b.encounter_boss_killed = 1
            WHERE ' . $where . '
            GROUP BY g.guild_id
            ORDER BY value DESC, g.guild_name
            LIMIT ' . self::LIMIT,
            $params
        );

        return \array_map(fn ($row) => [
            'name'   => $row['guild_name'],
            'link'   => '/guild/profile/' . $row['guild_id'],
            'class'  => '-',
            'mode'   => '-',
            'server' => $row['server_name'] ?? '-',
            'value'  => \number_format((int) $row['value']),
        ], $rows);
    }
}
