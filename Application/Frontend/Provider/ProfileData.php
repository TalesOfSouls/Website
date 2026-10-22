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
use Models\AccountMapper;
use Models\Character;
use Models\CharacterMapper;
use Models\Guild;
use Models\GuildMapper;
use Models\ServerMapper;

/**
 * Players (accounts), characters and guilds
 *
 * @package Application\Frontend\Provider
 * @since   1.0.0
 */
final class ProfileData extends DataAbstract
{
    /**
     * Entries per page in the lists
     *
     * @var int
     * @since 1.0.0
     */
    public const PER_PAGE = 50;

    /**
     * Character as array for the templates
     *
     * @param Character $character Character incl. class, server, guild and account
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function characterToArray(Character $character) : array
    {
        return [
            'id'          => $character->id,
            'name'        => $character->name,
            'player'      => $character->account->id,
            'player_name' => $character->account->name,
            'class'       => $character->class->name,
            'level'       => $character->getLevel(),
            'server'      => $character->server->name,
            'guild'       => $character->guild->id,
            'guild_name'  => $character->guild->name,
            'playtime'    => $character->getPlaytimeHours(),
            'item_level'  => $character->itemLevel,
            'season'      => $character->season,
            'dead'        => $character->dead,
            'hardcore'    => $character->hardcore,
            'created'     => $character->created,
        ];
    }

    /**
     * Load a character incl. relations
     *
     * @param int $id Character id
     *
     * @return null|Character
     *
     * @since 1.0.0
     */
    public function getCharacter(int $id) : ?Character
    {
        /** @var Character $character */
        $character = CharacterMapper::get()
            ->with('account')
            ->with('class')
            ->with('server')
            ->with('guild')
            ->with('race')
            ->where('id', $id)
            ->execute();

        return $character->id === 0 ? null : $character;
    }

    /**
     * Ladder rank of a character (by xp, seasonal characters are not ranked)
     *
     * @param Character $character Character
     *
     * @return null|int
     *
     * @since 1.0.0
     */
    public function getRank(Character $character) : ?int
    {
        if ($character->season !== null) {
            return null;
        }

        return 1 + (int) $this->value(
            'SELECT COUNT(*) FROM "character" WHERE character_season IS NULL AND character_xp > ?',
            [$character->xp]
        );
    }

    /**
     * Characters of an account (highest level first)
     *
     * @param int  $account        Account id
     * @param bool $includeSeasons Include seasonal characters
     *
     * @return array<int, array>
     *
     * @since 1.0.0
     */
    public function getCharactersOfAccount(int $account, bool $includeSeasons = false) : array
    {
        $mapper = CharacterMapper::getAll()
            ->with('account')
            ->with('class')
            ->with('server')
            ->with('guild')
            ->where('account', $account);

        if (!$includeSeasons) {
            $mapper->where('season', null);
        }

        $characters = [];
        foreach ($mapper->executeGetArray() as $character) {
            $characters[$character->id] = $this->characterToArray($character);
        }

        \uasort($characters, fn ($a, $b) => $b['level'] <=> $a['level'] ?: $a['id'] <=> $b['id']);

        return $characters;
    }

    /**
     * Load an account
     *
     * @param int $id Account id
     *
     * @return null|Account
     *
     * @since 1.0.0
     */
    public function getAccount(int $id) : ?Account
    {
        /** @var Account $account */
        $account = AccountMapper::get()->where('id', $id)->execute();

        return $account->id === 0 ? null : $account;
    }

    /**
     * Statistics of an account
     *
     * @param int $account Account id
     *
     * @return array{playtime:int, pvp_kills:int, dungeons:int, achievement_points:int}
     *
     * @since 1.0.0
     */
    public function getAccountStats(int $account) : array
    {
        $row = $this->rows(
            'SELECT
                COALESCE(SUM(c.character_playtime), 0) AS playtime,
                COALESCE(SUM(l.character_log_players_killed), 0) AS pvp_kills,
                COALESCE(SUM(l.character_log_dungeons_success), 0) AS dungeons
            FROM "character" c
            LEFT JOIN character_log l ON l.character_log_character = c.character_id
            WHERE c.character_account = ?',
            [$account]
        )[0] ?? [];

        return [
            'playtime'           => (int) \round(($row['playtime'] ?? 0) / 3600),
            'pvp_kills'          => (int) ($row['pvp_kills'] ?? 0),
            'dungeons'           => (int) ($row['dungeons'] ?? 0),
            'achievement_points' => (int) $this->value(
                'SELECT COALESCE(SUM(account_achievement_tier_tier * 10), 0) FROM account_achievement_tier WHERE account_achievement_tier_account = ?',
                [$account]
            ),
        ];
    }

    /**
     * Player list
     *
     * @param string $search Name filter
     * @param int    $page   Page (1 = first)
     *
     * @return array{rows:array, count:int}
     *
     * @since 1.0.0
     */
    public function getPlayers(string $search, int $page) : array
    {
        $where  = 'a.account_status = 1' . ($search === '' ? '' : ' AND a.account_name LIKE ?');
        $params = $search === '' ? [] : ['%' . $search . '%'];

        $count = (int) $this->value('SELECT COUNT(*) FROM account a WHERE ' . $where, $params);
        $rows  = $this->rows(
            'SELECT a.account_id AS id, a.account_name AS name, a.account_created AS created,
                (SELECT COUNT(*) FROM "character" c WHERE c.character_account = a.account_id AND c.character_season IS NULL) AS characters,
                (SELECT COALESCE(SUM(t.account_achievement_tier_tier * 10), 0) FROM account_achievement_tier t WHERE t.account_achievement_tier_account = a.account_id) AS points
            FROM account a
            WHERE ' . $where . '
            ORDER BY points DESC, a.account_name
            LIMIT ' . self::PER_PAGE . ' OFFSET ' . (($page - 1) * self::PER_PAGE),
            $params
        );

        return ['rows' => $rows, 'count' => $count];
    }

    /**
     * Character list
     *
     * @param string $search Name filter
     * @param int    $server Server filter (0 = all)
     * @param int    $page   Page (1 = first)
     *
     * @return array{rows:array, count:int}
     *
     * @since 1.0.0
     */
    public function getCharacters(string $search, int $server, int $page) : array
    {
        $mapper = CharacterMapper::getAll()
            ->with('account')
            ->with('class')
            ->with('server')
            ->with('guild')
            ->where('season', null);

        $count = CharacterMapper::count()->where('season', null);

        if ($search !== '') {
            $mapper->where('name', '%' . $search . '%', 'LIKE');
            $count->where('name', '%' . $search . '%', 'LIKE');
        }

        if ($server > 0) {
            $mapper->where('server', $server);
            $count->where('server', $server);
        }

        $rows = [];
        foreach ($mapper->sort('xp', 'DESC')->limit(self::PER_PAGE)->offset(($page - 1) * self::PER_PAGE)->executeGetArray() as $character) {
            $rows[] = $this->characterToArray($character);
        }

        return ['rows' => $rows, 'count' => $count->executeCount()];
    }

    /**
     * Servers that have characters (id => name)
     *
     * @return array<int, string>
     *
     * @since 1.0.0
     */
    public function getServers() : array
    {
        $servers = [];
        foreach ($this->rows('SELECT DISTINCT character_server FROM "character" WHERE character_server IS NOT NULL') as $row) {
            $servers[] = (int) $row['character_server'];
        }

        $names = [];
        foreach (ServerMapper::getAll()->executeGetArray() as $server) {
            if (\in_array($server->id, $servers, true)) {
                $names[$server->id] = $server->name;
            }
        }

        return $names;
    }

    /**
     * Load a guild incl. members (reserved guild names have no founding date and are not shown)
     *
     * @param int $id Guild id
     *
     * @return null|Guild
     *
     * @since 1.0.0
     */
    public function getGuild(int $id) : ?Guild
    {
        /** @var Guild $guild */
        $guild = GuildMapper::get()
            ->with('server')
            ->with('members')
            ->with('members/character')
            ->with('members/character/class')
            ->with('members/rank')
            ->with('members/role')
            ->with('ranks')
            ->where('id', $id)
            ->execute();

        return $guild->id === 0 || $guild->created === null ? null : $guild;
    }

    /**
     * Raid ranking of the guilds: killed raid bosses of guild groups (half of the group from the guild)
     *
     * @return array<int, int> guild id => rank
     *
     * @since 1.0.0
     */
    public function getRaidRanking() : array
    {
        $rows = $this->rows(
            'SELECT g.guild_id AS id, COUNT(DISTINCT b.encounter_boss_id) AS kills
            FROM guild g
            LEFT JOIN "character" c ON c.character_guild = g.guild_id
            LEFT JOIN encounter_member m ON m.encounter_member_character = c.character_id
            LEFT JOIN encounter e ON e.encounter_id = m.encounter_member_encounter AND e.encounter_type = \'raid\'
            LEFT JOIN encounter_boss b ON b.encounter_boss_encounter = e.encounter_id AND b.encounter_boss_killed = 1
            WHERE g.guild_created IS NOT NULL
            GROUP BY g.guild_id
            ORDER BY kills DESC, g.guild_name'
        );

        $ranking = [];
        foreach ($rows as $i => $row) {
            $ranking[(int) $row['id']] = $i + 1;
        }

        return $ranking;
    }

    /**
     * Guild list
     *
     * @param string $search Name filter
     *
     * @return array<int, array>
     *
     * @since 1.0.0
     */
    public function getGuilds(string $search) : array
    {
        $params = $search === '' ? [] : ['%' . $search . '%', '%' . $search . '%'];

        return $this->rows(
            'SELECT g.guild_id AS id, g.guild_name AS name, g.guild_tag AS tag, g.guild_recruiting AS recruiting, s.server_name AS server,
                (SELECT COUNT(*) FROM guild_member gm WHERE gm.guild_member_guild = g.guild_id) AS members
            FROM guild g
            LEFT JOIN server s ON s.server_id = g.guild_server
            WHERE g.guild_created IS NOT NULL' . ($search === '' ? '' : ' AND (g.guild_name LIKE ? OR g.guild_tag LIKE ?)') . '
            ORDER BY members DESC, g.guild_name',
            $params
        );
    }
}
