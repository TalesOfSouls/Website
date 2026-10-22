<?php
/**
 * Tales of Souls
 *
 * Dummy data for testing the website
 *
 * Fills the website tables with deterministic dummy data (accounts, characters, guilds, equipment,
 * dungeon runs, raids, world bosses, PvP matches, achievements, seasons, feedback, ...).
 *
 * Dummy rows use ids >= 1001 in the existing game tables (account, character, guild, ...).
 * The website-only tables (encounter_*, season_*, feedback_*, ...) are cleared completely.
 * Running the script again replaces the dummy data.
 *
 * All dummy accounts use the password "password".
 *
 * Usage: php Database/seed.php [path/to/TalesOfSouls.db]
 *
 * PHP Version 8.2
 *
 * @package   Database
 * @version   1.0.0
 */
declare(strict_types=1);

$path = $argv[1] ?? __DIR__ . '/../TalesOfSouls.db';

// make sure all tables/columns exist
$argv[1] = $path;
require __DIR__ . '/migrate.php';

$db = new \PDO('sqlite:' . $path);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA foreign_keys = OFF');
$db->beginTransaction();

\mt_srand(20261022);

const DUMMY_ID = 1001;
const NOW      = '2026-10-21 22:00:00';

/* ==========================================================================
   Helpers
   ========================================================================== */

/**
 * Insert a row and return the id
 *
 * @param string $table Table name
 * @param array  $row   Column (without table prefix) => value
 */
function insert(string $table, array $row) : int
{
    global $db;
    static $statements = [];

    $columns = \array_map(fn ($c) => '"' . $table . '_' . $c . '"', \array_keys($row));
    $key     = $table . ':' . \implode(',', $columns);

    $statements[$key] ??= $db->prepare(
        'INSERT INTO "' . $table . '" (' . \implode(',', $columns) . ') VALUES (' . \implode(',', \array_fill(0, \count($row), '?')) . ')'
    );

    $statements[$key]->execute(\array_values($row));

    return (int) $db->lastInsertId();
}

function rnd(int $min, int $max) : int
{
    return \mt_rand($min, $max);
}

function chance(int $percent) : bool
{
    return \mt_rand(1, 100) <= $percent;
}

function pick(array $list) : mixed
{
    return $list[\array_rand($list)];
}

function dt(\DateTimeInterface $date) : string
{
    return $date->format('Y-m-d H:i:s');
}

function randomDate(\DateTimeInterface $from, \DateTimeInterface $to) : \DateTime
{
    $start = $from->getTimestamp();
    $end   = \max($start + 1, $to->getTimestamp());

    return (new \DateTime())->setTimestamp(\mt_rand($start, $end));
}

/**
 * Deterministic shuffle using mt_rand (shuffle() doesn't use the seeded generator in all PHP versions)
 */
function mtShuffle(array $list) : array
{
    $list = \array_values($list);
    for ($i = \count($list) - 1; $i > 0; --$i) {
        $j        = \mt_rand(0, $i);
        $tmp      = $list[$i];
        $list[$i] = $list[$j];
        $list[$j] = $tmp;
    }

    return $list;
}

$now = new \DateTime(NOW);

/* ==========================================================================
   Remove old dummy data
   ========================================================================== */

foreach ([
    'account_achievement_tier', 'character_event', 'character_level_log',
    'encounter_score', 'encounter_boss_stat', 'encounter_attempt', 'encounter_boss', 'encounter_member_skill',
    'encounter_member', 'encounter', 'instance_boss', 'instance', 'pvp_mode',
    'season_boss_kill', 'season_progress', 'season_boss', 'season',
    'feedback_comment_vote', 'feedback_comment', 'feedback_vote', 'feedback_state_log', 'feedback',
    'achievement_category',
] as $table) {
    $db->exec('DELETE FROM "' . $table . '"');
    $db->exec('DELETE FROM sqlite_sequence WHERE name = \'' . $table . '\'');
}

$db->exec('DELETE FROM "account_achievement" WHERE account_achievement_account >= ' . DUMMY_ID);
$db->exec('DELETE FROM "item_individual" WHERE item_individual_character >= ' . DUMMY_ID);
$db->exec('DELETE FROM "character_equipment" WHERE character_equipment_character >= ' . DUMMY_ID);
$db->exec('DELETE FROM "character_proficiency" WHERE character_proficiency_character >= ' . DUMMY_ID);
$db->exec('DELETE FROM "character_log" WHERE character_log_character >= ' . DUMMY_ID);
$db->exec('DELETE FROM "guild_member" WHERE guild_member_character >= ' . DUMMY_ID . ' OR guild_member_guild >= ' . DUMMY_ID);
$db->exec('DELETE FROM "guild_rank" WHERE guild_rank_guild >= ' . DUMMY_ID);
$db->exec('DELETE FROM "character" WHERE character_id >= ' . DUMMY_ID);
$db->exec('DELETE FROM "guild" WHERE guild_id >= ' . DUMMY_ID);
$db->exec('DELETE FROM "account" WHERE account_id >= ' . DUMMY_ID);

/* ==========================================================================
   Game data that is needed by the website
   ========================================================================== */

// Skills used in the encounter statistics: cooldown (s), kind, target
$skillSetup = [
    'Fury Strikes'      => [0, 'damage', 'single'],
    'Backstab'          => [12, 'damage', 'single'],
    'Overpower'         => [30, 'damage', 'single'],
    'Cleave'            => [8, 'damage', 'aoe'],
    'Stomp'             => [20, 'damage', 'aoe'],
    'War Cry'           => [90, 'utility', 'single'],
    'Shield Slam'       => [0, 'damage', 'single'],
    'Taunt'             => [8, 'utility', 'single'],
    'Sonic Clap'        => [10, 'damage', 'aoe'],
    'Armor Break'       => [16, 'damage', 'single'],
    'Guard'             => [120, 'utility', 'single'],
    'Recovery'          => [60, 'heal', 'single'],
    'Heal'              => [0, 'heal', 'single'],
    'Regenerate Life'   => [10, 'heal', 'single'],
    'Healing Totem'     => [15, 'heal', 'aoe'],
    'Hymn'              => [180, 'heal', 'aoe'],
    'Lightning Strike'  => [6, 'damage', 'single'],
    'Water Stream'      => [12, 'damage', 'aoe'],
];

$skillIds = [];
$stmt     = $db->prepare('UPDATE skill SET skill_cooldown = ?, skill_kind = ?, skill_target = ? WHERE skill_name = ?');
foreach ($skillSetup as $name => $setup) {
    $stmt->execute([$setup[0], $setup[1], $setup[2], $name]);
    $skillIds[$name] = (int) $db->query('SELECT skill_id FROM skill WHERE skill_name = ' . $db->quote($name))->fetchColumn();
}

$roleSkills = [
    'DPS'    => ['Fury Strikes', 'Backstab', 'Overpower', 'Cleave', 'Stomp', 'War Cry'],
    'Tank'   => ['Shield Slam', 'Taunt', 'Sonic Clap', 'Armor Break', 'Guard', 'Recovery'],
    'Healer' => ['Heal', 'Regenerate Life', 'Healing Totem', 'Hymn', 'Lightning Strike', 'Water Stream'],
];

// Achievement categories (only set where no category is defined yet)
$categories = ['Progression', 'Combat', 'Exploration', 'Economy', 'Collection', 'PvP', 'Professions'];
$categoryIds = [];
foreach ($categories as $category) {
    $categoryIds[$category] = insert('achievement_category', ['name' => $category]);
}

$categoryKeywords = [
    'PvP'         => ['pvp', 'players'],
    'Combat'      => ['kill', 'die', 'mob', 'raid', 'monster'],
    'Exploration' => ['dungeon', 'explore', 'jumping', 'event', 'quest', 'heist', 'mount race'],
    'Economy'     => ['gold', 'trade', 'auction'],
    'Professions' => ['cook', 'fish', 'harvest', 'mine', 'plant', 'proficienc', 'breed', 'dye'],
    'Collection'  => ['loot', 'pets', 'item', 'rarity', 'tame', 'catch'],
    'Progression' => ['level', 'class'],
];

$achievementTypes = $db->query('SELECT * FROM achievement_type')->fetchAll(\PDO::FETCH_ASSOC);
$stmt = $db->prepare('UPDATE achievement_type SET achievement_type_category = ? WHERE achievement_type_id = ? AND achievement_type_category IS NULL');
foreach ($achievementTypes as $i => $type) {
    $text     = \strtolower(($type['achievement_type_name'] ?? '') . ' ' . ($type['achievement_type_desc'] ?? ''));
    $category = 'Progression';

    foreach ($categoryKeywords as $name => $keywords) {
        foreach ($keywords as $keyword) {
            if (\str_contains($text, $keyword)) {
                $category = $name;
                break 2;
            }
        }
    }

    $stmt->execute([$categoryIds[$category], $type['achievement_type_id']]);
}

// Lookups
$classes = [];
foreach ($db->query('SELECT class_id, class_name FROM class WHERE class_parent IS NOT NULL') as $row) {
    $classes[(int) $row['class_id']] = $row['class_name'];
}

$races   = \array_map('intval', $db->query('SELECT race_id FROM race')->fetchAll(\PDO::FETCH_COLUMN));
$servers = [2, 3, 4, 5];

$tanks   = ['Paladin', 'Knight', 'Berserker', 'Monk'];
$healers = ['Priest', 'Druid', 'Shaman', 'Bard', 'Totemist'];
$roleOf  = fn (string $class) : string => \in_array($class, $tanks) ? 'Tank' : (\in_array($class, $healers) ? 'Healer' : 'DPS');

// xp needed per level (the values are stored as text incl. thousands separators)
$levelXp = [];
foreach ($db->query('SELECT player_level_id, player_level_xp FROM player_level') as $row) {
    $levelXp[(int) $row['player_level_id']] = (int) \preg_replace('/\D/', '', (string) $row['player_level_xp']);
}

$xpForLevel = function (int $level) use ($levelXp) : int {
    $xp = 0;
    for ($l = 1; $l < $level; ++$l) {
        $xp += $levelXp[$l] ?? 0;
    }

    return $xp;
};

/* ==========================================================================
   Accounts
   ========================================================================== */

$syllables = [
    ['Al', 'Bran', 'Cor', 'Dra', 'El', 'Fen', 'Gal', 'Hal', 'Is', 'Jor', 'Kae', 'Lys', 'Mor', 'Nym', 'Or', 'Per', 'Quin', 'Rav', 'Syl', 'Tor', 'Ul', 'Val', 'Wyn', 'Yse', 'Zar', 'Ash', 'Bel', 'Cael', 'Dor', 'Eir'],
    ['a', 'e', 'i', 'o', 'u', 'ae', 'ia', 'y'],
    ['dric', 'lle', 'gar', 'vin', 'nor', 'ra', 'th', 'wen', 'ric', 'mir', 'dra', 'lin', 'nus', 'ssa', 'ven', 'rik', 'lia', 'mond', 'dan', 'wyn'],
];

$usedNames = [];
$makeName  = function () use ($syllables, &$usedNames) : string {
    do {
        $name = pick($syllables[0]) . (chance(60) ? pick($syllables[1]) : '') . pick($syllables[2]);
    } while (isset($usedNames[\strtolower($name)]));

    $usedNames[\strtolower($name)] = true;

    return $name;
};

// The accounts of the former sample data keep their names
$fixedAccounts = [
    1001 => ['Aldric', '2024-07-01'], 1002 => ['Mirelle', '2024-07-03'], 1003 => ['Thorgar', '2024-07-10'],
    1004 => ['Sylvara', '2024-08-02'], 1005 => ['Corvin', '2024-08-15'], 1006 => ['Kaelith', '2024-09-01'],
    1007 => ['Halys', '2024-09-20'], 1008 => ['Morgwen', '2024-07-02'],
];

foreach ($fixedAccounts as $fixed) {
    $usedNames[\strtolower($fixed[0])] = true;
}

$password = \password_hash('password', \PASSWORD_DEFAULT);
$accounts = [];
$accountCount = 120;

for ($id = DUMMY_ID; $id < DUMMY_ID + $accountCount; ++$id) {
    $name    = $fixedAccounts[$id][0] ?? $makeName();
    $created = isset($fixedAccounts[$id])
        ? new \DateTime($fixedAccounts[$id][1] . ' 18:00:00')
        : randomDate(new \DateTime('2024-07-01'), new \DateTime('2026-09-15'));

    // active players play a lot, others just started or only read the forums
    $activity = isset($fixedAccounts[$id]) ? 1.0 - ($id - DUMMY_ID) * 0.08 : \mt_rand(15, 100) / 100;

    $db->prepare('INSERT INTO account (account_id, account_name, account_email, account_password, account_created, account_lastlogin, account_status, account_role) VALUES (?, ?, ?, ?, ?, ?, 1, ?)')
        ->execute([
            $id, $name, \strtolower($name) . '@example.com', $password, dt($created),
            dt(randomDate(\max($created, new \DateTime('2026-09-01')), $now)),
            $id === 1008 ? 1 : 0, // Morgwen is a developer (can moderate the feedback board)
        ]);

    $accounts[$id] = ['id' => $id, 'name' => $name, 'created' => $created, 'activity' => $activity, 'characters' => []];
}

// Login accounts for testing
//  - tester / TestUser2026!   normal player account
//  - admin  / AdminPass2026!  administrator (news, wiki, FAQ, development, users, feedback moderation)
// spl1nes (account 1) is an administrator as well but has no password
$db->exec('UPDATE account SET account_role = 2, account_status = 1, account_created = COALESCE(account_created, \'2024-07-01 12:00:00\') WHERE account_id IN (1, 2)');
$db->prepare('UPDATE account SET account_password = ?, account_email = COALESCE(account_email, \'admin@example.com\') WHERE account_id = 2')
    ->execute([\password_hash('AdminPass2026!', \PASSWORD_DEFAULT)]);

$testerId = DUMMY_ID + $accountCount;
$db->prepare('INSERT INTO account (account_id, account_name, account_email, account_password, account_created, account_status, account_role) VALUES (?, \'tester\', \'tester@example.com\', ?, ?, 1, 0)')
    ->execute([$testerId, \password_hash('TestUser2026!', \PASSWORD_DEFAULT), dt((clone $now)->modify('-14 days'))]);
$usedNames['tester'] = true;

/* ==========================================================================
   Guilds
   ========================================================================== */

$guildDefs = [
    ['Ashen Covenant',   'ASH',  2, 'A raiding guild with a soft spot for world bosses. Raids Wednesday and Sunday evening.', [0, 1, 1, 1, 1], true],
    ['Moonlit Vanguard', 'MLV',  3, 'Relaxed PvE guild, timed dungeons every evening and new players are always welcome.',     [0, 0, 0, 1, 1], true],
    ['Iron Oath',        'IRON', 2, 'Siege specialists. We hold the line in World vs. World.',                                 [1, 1, 0, 0, 0], false],
    ['Silver Thorns',    'THRN', 4, 'Arena teams and capture the flag. Voice chat required.',                                  [0, 1, 0, 1, 0], true],
    ['Emberfall',        'EMB',  5, 'Hardcore raiding with progress in mind. Two raid groups.',                                 [0, 0, 1, 1, 1], false],
    ['The Wayfarers',    'WAY',  3, 'Explorers, collectors and crafters. We help each other with professions.',                [0, 0, 0, 1, 1], true],
    ['Nightwatch',       'NW',   4, 'Late night players from all time zones.',                                                  [1, 1, 1, 1, 1], true],
    ['Stormborn',        'STRM', 5, 'Seasonal content first: world dungeons and seasonal ladders.',                            [0, 1, 0, 1, 1], true],
];

$guilds = [];
foreach ($guildDefs as $i => $def) {
    $id      = DUMMY_ID + $i;
    $created = randomDate(new \DateTime('2024-07-05'), new \DateTime('2025-06-01'));

    $db->prepare('INSERT INTO guild (guild_id, guild_server, guild_name, guild_tag, guild_status, guild_wvw, guild_pvp, guild_raid, guild_dungeon, guild_seriousness, guild_pve, guild_description, guild_created, guild_recruiting) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([$id, $def[2], $def[0], $def[1], $def[4][0], $def[4][1], $def[4][2], $def[4][3], rnd(1, 3), $def[4][4], $def[3], dt($created), $def[5] ? 1 : 0]);

    $ranks = [];
    foreach ([['Guild Master', 16761600], ['Officer', 49151], ['Veteran', 11184810], ['Member', 16777215], ['Recruit', 8421504]] as $r) {
        $ranks[$r[0]] = insert('guild_rank', ['name' => $r[0], 'color' => $r[1], 'guild' => $id]);
    }

    $guilds[$id] = ['id' => $id, 'name' => $def[0], 'server' => $def[2], 'created' => $created, 'ranks' => $ranks, 'members' => []];
}

/* ==========================================================================
   Characters
   ========================================================================== */

$characters  = [];
$characterId = DUMMY_ID;

$createCharacter = function (array $account, array $options = []) use (&$characters, &$characterId, $makeName, $classes, $races, $servers, $xpForLevel, $now) : array {
    $id      = $characterId++;
    $classId = $options['class'] ?? \array_rand($classes);
    $created = $options['created'] ?? randomDate($account['created'], \min($now, (clone $account['created'])->modify('+90 days')));
    $level   = $options['level'];

    // playtime in seconds, later levels take longer
    $playtime = (int) (($level ** 1.45) * \mt_rand(55, 85) * 60 + ($level >= 99 ? \mt_rand(40, 260) * 3600 : 0));

    $character = [
        'id'       => $id,
        'account'  => $account['id'],
        'name'     => $options['name'] ?? $makeName(),
        'class_id' => $classId,
        'class'    => $classes[$classId],
        'race'     => $races[\array_rand($races)],
        'server'   => $options['server'] ?? $servers[\array_rand($servers)],
        'level'    => $level,
        'xp'       => $xpForLevel($level) + ($level < 99 ? \mt_rand(0, 400) : 0),
        'playtime' => $playtime,
        'created'  => $created,
        'season'   => $options['season'] ?? null,
        'hardcore' => $options['hardcore'] ?? false,
        'dead'     => $options['dead'] ?? false,
        'guild'    => null,
    ];

    $characters[$id] = $character;

    return $character;
};

// Former sample characters
$fixedCharacters = [
    1001 => ['Aldric', 'Paladin', 99], 1002 => ['Brannoc', 'Knight', 41], 1003 => ['Mirelle', 'Mage', 98],
    1004 => ['Ysolde', 'Priest', 63], 1005 => ['Thorgar', 'Berserker', 96], 1006 => ['Sylvara', 'Druid', 95],
    1007 => ['Fenrik', 'Hunter', 27], 1008 => ['Corvin', 'Necromancer', 88],
];
$fixedOwner = [1001 => 1001, 1002 => 1001, 1003 => 1002, 1004 => 1002, 1005 => 1003, 1006 => 1004, 1007 => 1004, 1008 => 1005];

foreach ($fixedCharacters as $id => $def) {
    $usedNames[\strtolower($def[0])] = true;
}

foreach ($fixedCharacters as $id => $def) {
    $characterId = $id;
    $c           = $createCharacter($accounts[$fixedOwner[$id]], [
        'name'  => $def[0],
        'class' => \array_search($def[1], $classes),
        'level' => $def[2],
        'server' => $id % 2 === 0 ? 3 : 2,
    ]);
    $accounts[$fixedOwner[$id]]['characters'][] = $c['id'];
}

$characterId = DUMMY_ID + 100;

foreach ($accounts as $accountId => $account) {
    if (!empty($account['characters'])) {
        continue;
    }

    $count = $account['activity'] > 0.7 ? rnd(1, 4) : rnd(1, 2);
    for ($c = 0; $c < $count; ++$c) {
        $maxLevel = (int) \min(99, \max(5, 99 * $account['activity'] * \mt_rand(70, 140) / 100));
        $level    = $c === 0 ? $maxLevel : rnd(5, \max(6, $maxLevel - 10));

        // most mains of active players are at the level cap
        if ($c === 0 && $account['activity'] > 0.6 && chance(70)) {
            $level = 99;
        }

        $character = $createCharacter($account, ['level' => $level, 'hardcore' => chance(8)]);

        if ($character['hardcore'] && chance(30)) {
            $characters[$character['id']]['dead'] = true;
        }

        $accounts[$accountId]['characters'][] = $character['id'];
    }
}

// Guild membership: the main character of most active accounts
$guildIds = \array_keys($guilds);
foreach ($accounts as $account) {
    foreach ($account['characters'] as $i => $cid) {
        if (($i === 0 && chance(75)) || ($i > 0 && chance(20))) {
            $guildId                     = $guildIds[$account['id'] % \count($guildIds)];
            $characters[$cid]['guild']   = $guildId;
            $characters[$cid]['server']  = $guilds[$guildId]['server'];
            $guilds[$guildId]['members'][] = $cid;
        }
    }
}

// Former sample guild memberships
foreach ([1001 => 1001, 1002 => 1001, 1003 => 1002, 1004 => 1002, 1005 => 1003, 1006 => 1002, 1008 => 1001] as $cid => $gid) {
    foreach ($guilds as $g => $guild) {
        $guilds[$g]['members'] = \array_values(\array_diff($guild['members'], [$cid]));
    }

    $characters[$cid]['guild']  = $gid;
    $characters[$cid]['server'] = $guilds[$gid]['server'];
    $guilds[$gid]['members'][]  = $cid;
}

$insertCharacter = function (array $c) use ($db) : void {
    $db->prepare('INSERT INTO "character" (character_id, character_account, character_name, character_title, character_server, character_class, character_xp, character_guild, character_playtime, character_race, character_created, character_season, character_hardcore, character_dead, character_item_level) VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)')
        ->execute([
            $c['id'], $c['account'], $c['name'], $c['server'], $c['class_id'], $c['xp'], $c['guild'], $c['playtime'],
            $c['race'], dt($c['created']), $c['season'], $c['hardcore'] ? 1 : 0, $c['dead'] ? 1 : 0,
        ]);
};

foreach ($characters as $c) {
    $insertCharacter($c);
}

// Guild members incl. role and rank
$roles = [];
foreach ($db->query('SELECT guild_role_id, guild_role_name FROM guild_role') as $row) {
    $roles[$row['guild_role_name']] = (int) $row['guild_role_id'];
}

foreach ($guilds as $guild) {
    $members = $guild['members'];
    \usort($members, fn ($a, $b) => $characters[$b]['level'] <=> $characters[$a]['level'] ?: $a <=> $b);

    // keep the former sample founders
    $founder = ['1001' => 1001, '1002' => 1003, '1003' => 1005][(string) $guild['id']] ?? $members[0] ?? null;

    foreach ($members as $i => $cid) {
        $isFounder = $cid === $founder;
        $rank      = $isFounder ? 'Guild Master' : ($i < 3 ? 'Officer' : ($characters[$cid]['level'] >= 90 ? 'Veteran' : ($characters[$cid]['level'] < 30 ? 'Recruit' : 'Member')));
        $role      = $isFounder ? 'Founder' : ($rank === 'Officer' ? 'Moderator' : 'Member');

        insert('guild_member', [
            'character' => $cid,
            'role'      => $roles[$role] ?? null,
            'rank'      => $guild['ranks'][$rank],
            'guild'     => $guild['id'],
            'joined'    => dt(\max($guild['created'], $characters[$cid]['created'])),
        ]);
    }
}

/* ==========================================================================
   Equipment, proficiencies, statistics, level progression
   ========================================================================== */

$slotColumns = [
    'Head' => 'head', 'Neck' => 'neck', 'Body' => 'body', 'Belt' => 'belt', 'Pants' => 'pants', 'Boots' => 'boots',
    'Ring' => 'ring1', 'Hand 1' => 'hand1', 'Hand 2' => 'hand2', 'Arms' => 'arms', 'Belt Attachment' => 'belt_attach',
    'Shoulder' => 'shoulder', 'Back' => 'back', 'Artifact' => 'artifact',
];

$equipmentTypes = [];
foreach ($db->query('SELECT equipment_type_id, equipment_type_name FROM equipment_type') as $row) {
    $equipmentTypes[$row['equipment_type_name']] = (int) $row['equipment_type_id'];
}

$slotItems = [
    'Head'            => ['Helmet', 'Circlet', 'Glasses'],
    'Neck'            => ['Necklace', 'Talisman'],
    'Body'            => ['Body'],
    'Belt'            => ['Belt'],
    'Pants'           => ['Pants'],
    'Boots'           => ['Boots'],
    'Ring'            => ['Ring'],
    'Hand 1'          => ['Longsword', 'Broadsword', 'Staff', 'Wand', 'Dagger', 'Heavy Mace', 'Longbow', 'Scepter', 'Katana', 'Spear'],
    'Hand 2'          => ['Shield', 'Orb', 'Grimoire', 'Quiver', 'Lantern', 'Dagger'],
    'Arms'            => ['Arms', 'Bracelet'],
    'Belt Attachment' => ['Belt Attachment', 'Flask', 'Gadget'],
    'Shoulder'        => [],
    'Back'            => ['Cape'],
    'Artifact'        => ['Runestone', 'Magic Crystal', 'Praying Beads'],
];

$prefixes = ['Runed', 'Ashen', 'Moonlit', 'Dragonscale', 'Steel', 'Frostforged', 'Ironbound', 'Shadow', 'Gilded', 'Ancient', 'Styx', 'Ember'];
$rarityNames = [1 => 'white', 2 => 'blue', 3 => 'yellow', 4 => 'orange', 5 => 'green', 6 => 'purple', 7 => 'red'];

$slots = [];
foreach ($db->query('SELECT equipment_slot_id, equipment_slot_name FROM equipment_slot') as $row) {
    $slots[(int) $row['equipment_slot_id']] = $row['equipment_slot_name'];
}

$loot = []; // character => [[date, name, rarity]]

foreach ($characters as $cid => $c) {
    $equipment = ['character' => $cid];
    $itemLevel = 0;

    foreach ($slots as $slotName) {
        if (!isset($slotColumns[$slotName]) || ($c['level'] < 30 && chance(25))) {
            continue;
        }

        $types    = $slotItems[$slotName];
        $typeName = empty($types) ? null : pick($types);
        $rarity   = \min(6, \max(1, (int) \floor($c['level'] / 22) + rnd(0, 2)));
        $level    = \max(1, $c['level'] - rnd(0, 4));
        $name     = pick($prefixes) . ' ' . ($typeName ?? 'Pauldrons');
        $date     = randomDate((clone $c['created'])->modify('+' . (int) ($c['level'] * 0.6) . ' days'), $now);

        $itemId = insert('item_individual', [
            'equipment_type' => $typeName === null ? null : ($equipmentTypes[$typeName] ?? null),
            'name'           => $name,
            'rarity'         => $rarity,
            'level'          => $level,
            'character'      => $cid,
            'created'        => dt($date),
        ]);

        $equipment[$slotColumns[$slotName]] = $itemId;
        $itemLevel += $level;

        if ($rarity >= 3) {
            $loot[$cid][] = [$date, $name, $rarity];
        }
    }

    insert('character_equipment', $equipment);
    $db->exec('UPDATE "character" SET character_item_level = ' . $itemLevel . ' WHERE character_id = ' . $cid);
    $characters[$cid]['item_level'] = $itemLevel;
}

$professionColumns = ['brewing', 'forging', 'enchanting', 'cooking', 'tailoring', 'breeding', 'taming', 'mining', 'woodcutting', 'gathering', 'stealing', 'stealth', 'archeology', 'fishing', 'fletching', 'diving', 'farming', 'endurance'];
$professions       = [];

foreach ($characters as $cid => $c) {
    $row     = ['character' => $cid];
    $chosen  = \array_slice(mtShuffle($professionColumns), 0, 3);

    foreach ($professionColumns as $p) {
        $row[$p] = 0;
    }

    foreach ($chosen as $i => $p) {
        $row[$p]                 = (int) \min(400, \max(10, $c['level'] * \mt_rand(200, 420) / 100 - $i * 40));
        $professions[$cid][$p]   = $row[$p];
    }

    insert('character_proficiency', $row);
}

// level progression: level => [hours, date]
$progression = [];
foreach ($characters as $cid => $c) {
    $weights = [];
    for ($l = 2; $l <= $c['level']; ++$l) {
        $weights[$l] = $l ** 1.35 * \mt_rand(80, 120) / 100;
    }

    $sum          = \max(1, \array_sum($weights));
    $hoursTotal   = $c['playtime'] / 3600;
    $levelingTime = $c['level'] >= 99 ? $hoursTotal * 0.55 : $hoursTotal * 0.92;
    $end          = $c['season'] !== null ? \min($now, $c['season_end'] ?? $now) : $now;
    $days         = \max(20, (int) $c['created']->diff($end)->days);
    $hoursPerDay  = $hoursTotal / $days;

    $progression[$cid] = [1 => [0.0, clone $c['created']]];
    insert('character_level_log', ['character' => $cid, 'level' => 1, 'playtime' => 0, 'datetime' => dt($c['created'])]);

    $hours = 0.0;
    foreach ($weights as $l => $weight) {
        $hours += $levelingTime * $weight / $sum;
        $date   = (clone $c['created'])->modify('+' . (int) ($hours / \max(0.1, $hoursPerDay) * 24 * 60) . ' minutes');
        $date   = \min($date, $end);

        $progression[$cid][$l] = [\round($hours, 1), $date];
        insert('character_level_log', ['character' => $cid, 'level' => $l, 'playtime' => \round($hours, 1), 'datetime' => dt($date)]);
    }
}

/* ==========================================================================
   Instances (dungeons, raids, world bosses, PvP maps)
   ========================================================================== */

$instanceDefs = [
    'dungeon' => [
        ['Sunken Crypt', '', 5, ['Drowned Warden', 'Mother of Eels', 'The Pale Abbot']],
        ['Ashen Halls', '', 5, ['Cinder Hound', 'Forgemaster Ulgar', 'The Ember Queen']],
        ['Moonwood Hollow', '', 5, ['Thornback', 'Whispering Dryad', 'Moonfang']],
        ['Frostmere Depths', '', 5, ['Ice Golem', 'Hrimthur', 'Vael the Frozen']],
        ['Iron Deep', '', 5, ['Rust Crawler', 'Overseer Grom', 'The Deep Engine']],
    ],
    'raid' => [
        ['Hall of Ashes', '', 10, ['Pyre Sentinel', 'Twin Cinders', 'Archon of Embers', 'Ashborn Tyrant']],
        ['Spire of the Styx', '', 10, ['Soul Ferryman', 'The Weeping Choir', 'Gatekeeper Morr', 'Lady of the Riverbed', 'The Styx Incarnate']],
    ],
    'worldboss' => [
        ['Gorrath the Undying', 'Ashen Wastes', 40, ['Gorrath the Undying']],
        ['Frost Wyrm Vael', 'Frostmere', 40, ['Frost Wyrm Vael']],
        ['The Drowned King', 'Port Sorrow', 40, ['The Drowned King']],
        ['Elder Treant Ysmir', 'Moonwood', 40, ['Elder Treant Ysmir']],
    ],
];

$instances = [];
foreach ($instanceDefs as $type => $defs) {
    foreach ($defs as $def) {
        $instanceId = insert('instance', ['type' => $type, 'name' => $def[0], 'location' => $def[1], 'size' => $def[2], 'pvp_mode' => null]);
        $bosses     = [];

        foreach ($def[3] as $order => $boss) {
            $bosses[] = ['id' => insert('instance_boss', ['instance' => $instanceId, 'name' => $boss, 'order' => $order + 1]), 'name' => $boss];
        }

        $instances[$type][] = ['id' => $instanceId, 'name' => $def[0], 'location' => $def[1], 'size' => $def[2], 'bosses' => $bosses];
    }
}

$pvpModes = [
    ['Arena 2v2', 2, 3, 'rounds', ['Bloodsand Pit', 'Frozen Ring']],
    ['Arena 3v3', 3, 3, 'rounds', ['Bloodsand Pit', 'Frozen Ring', 'Ruins of Vael']],
    ['Capture the Flag', 8, 3, 'captures', ['Twin Towers', 'Moonwood Glade']],
    ['Team Deathmatch', 6, 50, 'kills', ['Ashen Crossroads', 'Sunken Docks']],
    ['Capture the Objectives', 8, 1000, 'points', ['Iron Fields', 'Styx Delta']],
    ['Siege', 10, 5, 'gates', ['Ironhold', 'Frostmere Keep']],
];

foreach ($pvpModes as $def) {
    $modeId = insert('pvp_mode', ['name' => $def[0], 'size' => $def[1], 'target' => $def[2], 'unit' => $def[3]]);

    foreach ($def[4] as $map) {
        $instances['pvp'][] = [
            'id'     => insert('instance', ['type' => 'pvp', 'name' => $map, 'location' => $map, 'size' => $def[1] * 2, 'pvp_mode' => $modeId]),
            'name'   => $map,
            'mode'   => ['name' => $def[0], 'size' => $def[1], 'target' => $def[2], 'unit' => $def[3]],
            'bosses' => [],
        ];
    }
}

/* ==========================================================================
   Encounters
   ========================================================================== */

// Characters that can take part (normal characters, by level)
$endgame = \array_values(\array_filter($characters, fn ($c) => $c['level'] >= 60 && $c['season'] === null && !$c['dead']));
$anyChar = \array_values(\array_filter($characters, fn ($c) => $c['level'] >= 20 && $c['season'] === null && !$c['dead']));

// The main character of every account (highest level)
$mains = [];
foreach ($accounts as $account) {
    $best = null;
    foreach ($account['characters'] as $cid) {
        if ($best === null || $characters[$cid]['level'] > $characters[$best]['level']) {
            $best = $cid;
        }
    }

    if ($best !== null && $characters[$best]['level'] >= 60) {
        $mains[] = $best;
    }
}

// active players take part more often
$weightedMains = [];
foreach ($mains as $cid) {
    $times = 1 + (int) ($accounts[$characters[$cid]['account']]['activity'] * 5);
    for ($i = 0; $i < $times; ++$i) {
        $weightedMains[] = $cid;
    }
}

/**
 * Group around an anchor character, guild mates are preferred
 */
$buildGroup = function (int $anchor, int $size, array $pool) use ($characters, $roleOf) : array {
    $guild    = $characters[$anchor]['guild'];
    $mates    = [];
    $others   = [];

    foreach (mtShuffle($pool) as $c) {
        if ($c['id'] === $anchor) {
            continue;
        }

        if ($guild !== null && $c['guild'] === $guild) {
            $mates[] = $c['id'];
        } else {
            $others[] = $c['id'];
        }
    }

    // dungeons and raids are mostly played with guild mates
    $mateCount = (int) \round(($size - 1) * (\mt_rand(40, 90) / 100));
    $ids       = \array_merge([$anchor], \array_slice($mates, 0, $mateCount));
    $ids       = \array_merge($ids, \array_slice($others, 0, $size - \count($ids)));

    $group = [];
    foreach ($ids as $cid) {
        $group[] = ['character' => $cid, 'role' => $roleOf($characters[$cid]['class'])];
    }

    return $group;
};

/**
 * Detailed statistics incl. skill usage (see the former SampleEncounters)
 */
$detailedStats = function (array $group, int $duration) use ($roleSkills, $skillSetup) : array {
    $totalHealing = \array_sum(\array_column($group, 'healing'));
    $weights      = [];

    foreach ($group as $i => $member) {
        $weights[$i] = ($member['role'] === 'Tank' ? 4.0 : 1.0) * \mt_rand(70, 130) / 100;
    }

    $weightSum = \array_sum($weights);

    foreach ($group as $i => $member) {
        $aoeShare = $member['role'] === 'DPS' ? \mt_rand(15, 60) : \mt_rand(20, 50);
        $aoeHeal  = $member['role'] === 'Healer' ? \mt_rand(25, 60) : \mt_rand(0, 20);

        $group[$i]['damage_aoe']       = (int) ($member['damage'] * $aoeShare / 100);
        $group[$i]['damage_single']    = $member['damage'] - $group[$i]['damage_aoe'];
        $group[$i]['healing_aoe']      = (int) ($member['healing'] * $aoeHeal / 100);
        $group[$i]['healing_single']   = $member['healing'] - $group[$i]['healing_aoe'];
        $group[$i]['healing_received'] = (int) ($totalHealing * $weights[$i] / $weightSum);
        $group[$i]['damage_taken']     = (int) ($group[$i]['healing_received'] * \mt_rand(95, 125) / 100);

        // skill usage
        $gcd        = 1.5;
        $efficiency = \mt_rand(62, 98);
        $skills     = [];
        $cdPossible = 0;
        $cdCasts    = 0;
        $busy       = 0.0;

        foreach ($roleSkills[$member['role']] as $name) {
            $cd = $skillSetup[$name][0];
            if ($cd > 0) {
                $possible = (int) \floor($duration / $cd) + 1;
                $used     = \max(0, \min($possible, (int) \round($possible * \min(100, $efficiency + \mt_rand(-15, 10)) / 100)));

                $cdPossible += $possible;
                $cdCasts    += $used;
            } else {
                $possible = 0;
                $used     = 0;
            }

            $busy    += $used * $gcd;
            $skills[] = ['name' => $name, 'cd' => $cd, 'kind' => $skillSetup[$name][1], 'target' => $skillSetup[$name][2], 'possible' => $possible, 'casts' => $used, 'amount' => 0];
        }

        foreach ($skills as $s => $skill) {
            if ($skill['cd'] === 0) {
                $possible               = (int) \max(1, \floor(($duration - $busy) / $gcd));
                $skills[$s]['possible'] = $possible;
                $skills[$s]['casts']    = (int) \round($possible * $efficiency / 100);
            }
        }

        $pools = [
            'damage-single' => $group[$i]['damage_single'],
            'damage-aoe'    => $group[$i]['damage_aoe'],
            'heal-single'   => $group[$i]['healing_single'],
            'heal-aoe'      => $group[$i]['healing_aoe'],
        ];

        foreach ($pools as $key => $amount) {
            [$kind, $target] = \explode('-', $key);

            $skillWeights = [];
            foreach ($skills as $s => $skill) {
                if ($skill['kind'] === $kind && $skill['target'] === $target && $skill['casts'] > 0) {
                    $skillWeights[$s] = $skill['casts'] * (1 + $skill['cd'] / 6);
                }
            }

            $sum = \array_sum($skillWeights);
            foreach ($skillWeights as $s => $weight) {
                $skills[$s]['amount'] = (int) ($amount * $weight / $sum);
            }
        }

        $casts = \array_sum(\array_column($skills, 'casts'));

        $group[$i]['skills']         = $skills;
        $group[$i]['casts']          = $casts;
        $group[$i]['apm']            = \round($casts / \max(1, $duration / 60), 1);
        $group[$i]['cooldown_usage'] = $cdPossible > 0 ? (int) \round($cdCasts / $cdPossible * 100) : 100;
        $group[$i]['active']         = (int) \min(100, \round($casts * $gcd / \max(1, $duration) * 100));
    }

    return $group;
};

$saveMembers = function (int $encounterId, array $group) use ($skillIds) : array {
    $memberIds = [];

    foreach ($group as $i => $m) {
        $memberIds[$i] = insert('encounter_member', [
            'encounter'        => $encounterId,
            'character'        => $m['character'],
            'team'             => $m['team'] ?? 1,
            'role'             => $m['role'],
            'damage'           => $m['damage'],
            'damage_single'    => $m['damage_single'],
            'damage_aoe'       => $m['damage_aoe'],
            'healing'          => $m['healing'],
            'healing_single'   => $m['healing_single'],
            'healing_aoe'      => $m['healing_aoe'],
            'healing_received' => $m['healing_received'],
            'damage_taken'     => $m['damage_taken'],
            'deaths'           => $m['deaths'],
            'kills'            => $m['kills'] ?? 0,
            'assists'          => $m['assists'] ?? 0,
            'objectives'       => $m['objectives'] ?? 0,
            'casts'            => $m['casts'],
            'apm'              => $m['apm'],
            'cooldown_usage'   => $m['cooldown_usage'],
            'active'           => $m['active'],
            'rating_before'    => $m['rating_before'] ?? null,
            'rating_change'    => $m['rating_change'] ?? null,
        ]);

        foreach ($m['skills'] as $skill) {
            insert('encounter_member_skill', [
                'member'   => $memberIds[$i],
                'skill'    => $skillIds[$skill['name']],
                'casts'    => $skill['casts'],
                'possible' => $skill['possible'],
                'amount'   => $skill['amount'],
            ]);
        }
    }

    return $memberIds;
};

$participation = []; // character => [[date, type, encounter id, title, detail]]

// Dates of all encounters (spread over the last ~5 months, evenings)
$encounterDate = function () use ($now) : \DateTime {
    $date = (clone $now)->modify('-' . \mt_rand(0, 150) . ' days');

    return $date->setTime(\mt_rand(17, 22), \mt_rand(0, 11) * 5);
};

$counts = ['dungeon' => 220, 'raid' => 70, 'worldboss' => 60];

foreach ($counts as $type => $count) {
    for ($e = 0; $e < $count; ++$e) {
        $instance = pick($instances[$type]);
        $anchor   = pick($weightedMains);
        $date     = $encounterDate();

        if ($type === 'dungeon') {
            $difficulty = chance(33) ? 'Heroic' : 'Timed +' . rnd(2, 15);
            $success    = chance(85);
            $maxTries   = 2;
            $group      = $buildGroup($anchor, 5, $endgame);
        } elseif ($type === 'raid') {
            $difficulty = chance(50) ? 'Normal' : 'Heroic';
            $success    = chance(65);
            $maxTries   = 18;
            $group      = $buildGroup($anchor, 10, $endgame);
        } else {
            $difficulty = 'World';
            $success    = chance(90);
            $maxTries   = 1;
            $group      = $buildGroup($anchor, rnd(18, 32), $anyChar);
        }

        // Bosses & attempts
        $bossDefs = $instance['bosses'];
        $lastBoss = $success ? \count($bossDefs) - 1 : rnd(0, \count($bossDefs) - 1);
        $bosses   = [];
        $duration = 0;

        foreach ($bossDefs as $b => $boss) {
            if ($b > $lastBoss) {
                $bosses[] = ['boss' => $boss['id'], 'killed' => false, 'attempts' => []];
                continue;
            }

            $kill     = $success || $b < $lastBoss;
            $max      = $maxTries + ($kill ? 0 : 1);
            $tries    = \min(rnd(1, $max), rnd(1, $max));
            $hp       = (float) rnd(55, 90);
            $attempts = [];

            for ($a = 1; $a <= $tries; ++$a) {
                $isKill     = $kill && $a === $tries;
                $attempts[] = [
                    'no'       => $a,
                    'duration' => $isKill ? rnd(240, 420) : rnd(90, 360),
                    'hp_left'  => $isKill ? 0 : (int) \max(2, \round($hp)),
                    'kill'     => $isKill,
                ];

                $hp = \max(2.0, $hp * rnd(78, 97) / 100 + rnd(-2, 6));
            }

            $bosses[] = ['boss' => $boss['id'], 'killed' => $kill, 'attempts' => $attempts];
            $duration += \array_sum(\array_column($attempts, 'duration')) + ($type === 'worldboss' ? 0 : rnd(120, 420));
        }

        foreach ($group as $i => $member) {
            $dps = match ($member['role']) { 'Tank' => rnd(1200, 2000), 'Healer' => rnd(300, 700), default => rnd(2500, 4500) };
            $hps = match ($member['role']) { 'Tank' => rnd(50, 200), 'Healer' => rnd(2000, 3500), default => rnd(0, 120) };

            $group[$i] += [
                'damage'  => $dps * $duration,
                'healing' => $hps * $duration,
                'dps'     => $dps,
                'deaths'  => chance(70) ? 0 : rnd(1, $success ? 2 : 4),
            ];
        }

        $group = $detailedStats($group, $duration);

        $encounterId = insert('encounter', [
            'type'       => $type,
            'instance'   => $instance['id'],
            'difficulty' => $difficulty,
            'start'      => dt($date),
            'duration'   => $duration,
            'success'    => $success ? 1 : 0,
            'winner'     => null,
            'score1'     => null,
            'score2'     => null,
        ]);

        $memberIds = $saveMembers($encounterId, $group);

        foreach ($bosses as $boss) {
            $bossId = insert('encounter_boss', ['encounter' => $encounterId, 'boss' => $boss['boss'], 'killed' => $boss['killed'] ? 1 : 0]);

            foreach ($boss['attempts'] as $attempt) {
                insert('encounter_attempt', [
                    'boss' => $bossId, 'no' => $attempt['no'], 'duration' => $attempt['duration'],
                    'hp_left' => $attempt['hp_left'], 'kill' => $attempt['kill'] ? 1 : 0,
                ]);
            }

            $last = \end($boss['attempts']);
            if ($last === false) {
                continue;
            }

            foreach ($group as $i => $member) {
                $factor = $last['duration'] / \max(1, $duration);

                insert('encounter_boss_stat', [
                    'boss'     => $bossId,
                    'member'   => $memberIds[$i],
                    'damage'   => (int) ($member['dps'] * $last['duration'] * rnd(85, 115) / 100),
                    'healing'  => (int) ($member['healing'] * $factor * rnd(85, 115) / 100),
                    'received' => (int) ($member['healing_received'] * $factor * rnd(85, 115) / 100),
                    'taken'    => (int) ($member['damage_taken'] * $factor * rnd(85, 115) / 100),
                ]);
            }
        }

        $killed = \count(\array_filter($bosses, fn ($b) => $b['killed']));
        foreach ($group as $member) {
            [$title, $detail] = match ($type) {
                'dungeon' => [($success ? 'Cleared ' : 'Failed ') . $instance['name'], $difficulty . ' · ' . \gmdate($duration >= 3600 ? 'G:i:s' : 'i:s', $duration)],
                'raid'    => [$instance['name'] . ' ' . $killed . '/' . \count($bosses), $difficulty . ' · ' . \gmdate($duration >= 3600 ? 'G:i:s' : 'i:s', $duration)],
                default   => [($success ? 'Defeated ' : 'Fought ') . $instance['name'], $instance['location'] . ' · ' . \count($group) . ' players'],
            };

            $participation[$member['character']][] = [$date, $type, $encounterId, $title, $detail];
        }
    }
}

// PvP matches: generated first, the ratings are calculated in chronological order afterwards
$pvpCount = 260;
$matches  = [];
$pvpPool  = $endgame;

for ($e = 0; $e < $pvpCount; ++$e) {
    $map    = pick($instances['pvp']);
    $mode   = $map['mode'];
    $anchor = pick($weightedMains);

    // both teams from one pool, so nobody plays on both sides
    $players = $buildGroup($anchor, $mode['size'] * 2, $pvpPool);
    $teams   = [1 => \array_slice($players, 0, $mode['size']), 2 => \array_slice($players, $mode['size'], $mode['size'])];
    $winner  = chance(55) ? 1 : 2;
    $loser   = 3 - $winner;

    $target   = $mode['target'];
    $step     = \max(1, (int) \round($target / 25));
    $score    = [1 => 0, 2 => 0];
    $time     = 0;
    $timeline = [[0, 0, 0]];

    while ($score[$winner] < $target) {
        $time += rnd(20, $target <= 5 ? 180 : 60);
        $team  = chance(58) ? $winner : $loser;
        if ($team === $loser && $score[$loser] + $step >= $target) {
            $team = $winner;
        }

        $score[$team] = \min($target, $score[$team] + $step);
        $timeline[]   = [$time, $score[1], $score[2]];
    }

    $duration = $time + rnd(5, 30);
    $members  = [];

    foreach ($teams as $side => $list) {
        $won = $side === $winner;

        foreach ($list as $member) {
            $dps = match ($member['role']) { 'Tank' => rnd(900, 1600), 'Healer' => rnd(250, 600), default => rnd(1800, 3200) };
            $hps = $member['role'] === 'Healer' ? rnd(1500, 2800) : rnd(0, 150);

            $member += [
                'team'       => $side,
                'kills'      => (int) \round(rnd($won ? 2 : 0, $won ? 12 : 8) * ($member['role'] === 'Healer' ? 0.4 : 1)),
                'deaths'     => rnd($won ? 0 : 2, $won ? 6 : 10),
                'assists'    => rnd(1, 14),
                'damage'     => $dps * $duration,
                'healing'    => $hps * $duration,
                'objectives' => $mode['unit'] === 'kills' ? 0 : rnd(0, 4),
            ];

            $members[$side][] = $member;
        }
    }

    $members[1] = $detailedStats($members[1], $duration);
    $members[2] = $detailedStats($members[2], $duration);

    $matches[] = [
        'date' => $encounterDate(), 'map' => $map, 'mode' => $mode, 'duration' => $duration, 'winner' => $winner,
        'score' => $score, 'timeline' => $timeline, 'members' => \array_merge($members[1], $members[2]),
    ];
}

\usort($matches, fn ($a, $b) => $a['date'] <=> $b['date']);

$ratings = [];
foreach ($matches as $match) {
    foreach ($match['members'] as $i => $member) {
        $cid    = $member['character'];
        $before = $ratings[$cid] ??= 1400 + ($cid % 7) * 40;
        $change = ($member['team'] === $match['winner'] ? 1 : -1) * rnd(8, 24);

        $match['members'][$i]['rating_before'] = $before;
        $match['members'][$i]['rating_change'] = $change;
        $ratings[$cid]                          = $before + $change;
    }

    $encounterId = insert('encounter', [
        'type'       => 'pvp',
        'instance'   => $match['map']['id'],
        'difficulty' => $match['mode']['name'],
        'start'      => dt($match['date']),
        'duration'   => $match['duration'],
        'success'    => null,
        'winner'     => $match['winner'],
        'score1'     => $match['score'][1],
        'score2'     => $match['score'][2],
    ]);

    $saveMembers($encounterId, $match['members']);

    foreach ($match['timeline'] as $point) {
        insert('encounter_score', ['encounter' => $encounterId, 'time' => $point[0], 'score1' => $point[1], 'score2' => $point[2]]);
    }

    foreach ($match['members'] as $member) {
        $won   = $member['team'] === $match['winner'];
        $own   = $match['score'][$member['team']];
        $enemy = $match['score'][3 - $member['team']];

        $participation[$member['character']][] = [
            $match['date'], 'pvp', $encounterId,
            ($won ? 'Won ' : 'Lost ') . $match['mode']['name'],
            $match['map']['name'] . ' · ' . $own . ' : ' . $enemy . ' · rating ' . ($member['rating_change'] >= 0 ? '+' : '') . $member['rating_change'],
        ];
    }
}

/* ==========================================================================
   Character statistics (character_log)
   ========================================================================== */

foreach ($characters as $cid => $c) {
    $list = $participation[$cid] ?? [];
    $byType = \array_count_values(\array_column($list, 1));

    insert('character_log', [
        'character'        => $cid,
        'gold'             => (int) ($c['level'] ** 2 * rnd(8, 30)),
        'playtime'         => $c['playtime'],
        'chat'             => rnd(0, 4000),
        'mobs_killed'      => (int) ($c['level'] ** 1.6 * rnd(8, 20)),
        'players_killed'   => rnd(0, 40) * ($c['level'] >= 60 ? 6 : 1),
        'dungeons'         => $byType['dungeon'] ?? 0,
        'dungeons_success' => (int) (($byType['dungeon'] ?? 0) * 0.85),
        'dungeons_quit'    => rnd(0, 3),
        'raids'            => $byType['raid'] ?? 0,
        'raids_success'    => (int) (($byType['raid'] ?? 0) * 0.65),
        'raids_quit'       => rnd(0, 2),
        'pvp'              => $byType['pvp'] ?? 0,
        'pvp_quit'         => rnd(0, 4),
    ]);
}

/* ==========================================================================
   Achievements
   ========================================================================== */

$tierPoints = [10, 20, 30, 40, 50];
$achievementTiers = []; // account => [[date, name, tier]]

foreach ($accounts as $account) {
    $start    = $account['created']->getTimestamp();
    $span     = \max(1, $now->getTimestamp() - $start);
    $activity = \max(0.2, $account['activity']);

    foreach ($achievementTypes as $type) {
        $thresholds = [];
        for ($t = 1; $t <= 5; ++$t) {
            if (($value = $type['achievement_type_score' . $t]) !== null && $value !== '') {
                $thresholds[] = (int) $value;
            }
        }

        $thresholds = \array_values(\array_unique($thresholds));
        \sort($thresholds);

        if (empty($thresholds)) {
            $thresholds = [1];
        }

        $tiers    = \count($thresholds);
        $position = \min($tiers + 0.2, \mt_rand(0, (int) ($tiers * 104)) / 100 * $activity + \mt_rand(0, 100) / 100 * 0.5);
        $reached  = (int) \floor(\min($tiers, $position));

        if ($reached === 0 && $position < 0.2) {
            continue; // not started
        }

        if ($reached >= $tiers) {
            $progress = \end($thresholds);
        } else {
            $lower    = $reached === 0 ? 0 : $thresholds[$reached - 1];
            $progress = (int) ($lower + ($thresholds[$reached] - $lower) * ($position - $reached));
        }

        $cursor = $start + (int) ($span * \mt_rand(0, 25) / 100);
        $last   = null;

        for ($t = 1; $t <= $reached; ++$t) {
            $cursor = \min($now->getTimestamp(), $cursor + (int) ($span * \mt_rand(3, 18) / 100));
            $last   = (new \DateTime())->setTimestamp($cursor);

            insert('account_achievement_tier', ['account' => $account['id'], 'type' => $type['achievement_type_id'], 'tier' => $t, 'datetime' => dt($last)]);
            $achievementTiers[$account['id']][] = [$last, $type['achievement_type_name'] ?: $type['achievement_type_desc'], $t];
        }

        insert('account_achievement', [
            'account'  => $account['id'],
            'type'     => $type['achievement_type_id'],
            'score'    => $progress,
            'data'     => null,
            'datetime' => dt($last ?? randomDate($account['created'], $now)),
        ]);
    }
}

/* ==========================================================================
   Seasons
   ========================================================================== */

$seasonDefs = [
    ['Season of Embers',   '2024-07-01', '2024-10-31', 'The Cinder Vale',    6, 10, ['Ignar, the Ash Tyrant'], false, 'Ember rifts closed'],
    ['Season of Tides',    '2024-11-01', '2025-03-31', 'The Drowned Reach',  7, 12, ['Thalassa', 'The Leviathan'], true, 'Tidal shrines cleansed'],
    ['Season of Thorns',   '2025-04-01', '2025-07-15', 'The Briar Maze',     6, 11, ['The Thorn Queen'], true, 'Overgrown camps burned'],
    ['Season of Frost',    '2025-07-16', '2025-12-31', 'Rimeholt',           9, 15, ['Jarl Vinterkald', 'Skadi the Huntress', 'Hrimgar the Pale'], true, 'Frozen caravans rescued'],
    ['Season of Shadows',  '2026-01-01', '2026-04-30', 'The Umbral Hollow',  7, 12, ['Nyx', 'The Faceless One'], true, 'Shadow gates sealed'],
    ['Season of the Styx', '2026-05-01', '2026-08-31', 'The Styx Depths',    8, 14, ['Charon'], true, 'Lost souls ferried'],
    ['Season of Storms',   '2026-09-01', '2027-01-31', 'The Thunder Steppe', 8, 13, ['Voltara', 'Storm King Arkon'], true, 'Lightning totems captured'],
];

$divisions = ['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Champion'];
$seasonCharacters = [];

foreach ($seasonDefs as $s => $def) {
    $seasonId = insert('season', [
        'name' => $def[0], 'start' => $def[1] . ' 00:00:00', 'end' => $def[2] . ' 23:59:59', 'dungeon' => $def[3],
        'zones' => $def[4], 'elites' => $def[5], 'ladder' => $def[7] ? 1 : 0, 'event' => $def[8],
    ]);

    $bossIds = [];
    foreach ($def[6] as $order => $name) {
        $bossIds[] = insert('season_boss', ['season' => $seasonId, 'name' => $name, 'order' => $order + 1]);
    }

    $start   = new \DateTime($def[1]);
    $end     = new \DateTime($def[2] . ' 23:59:59');
    $current = $start <= $now && $end >= $now;

    if ($start > $now) {
        continue;
    }

    $elapsed = \min(1.0, ($now->getTimestamp() - $start->getTimestamp()) / \max(1, $end->getTimestamp() - $start->getTimestamp()));
    $ladderPlayers = 0;
    $ladder        = [];

    foreach ($accounts as $account) {
        if ($account['created'] > $end || !chance((int) (35 + $account['activity'] * 60))) {
            continue;
        }

        $from  = \max($start, $account['created']);
        $until = \min($now, $end);
        $span  = \max(86400, $until->getTimestamp() - $from->getTimestamp());
        $skill = \min(1.0, $account['activity'] * rnd(60, 120) / 100) * ($current ? \min(1.0, $elapsed * 1.6) : 1.0);

        // seasonal characters (a new level 1 character is needed to enter the world dungeon)
        $count = chance(25) ? 2 : 1;
        $mainLevel = 0;
        for ($c = 0; $c < $count; ++$c) {
            $level = $c === 0 ? (int) \max(5, \min(60, \round(60 * \min(1.0, $skill * 1.25)))) : rnd(8, 35);
            $mainLevel = \max($mainLevel, $level);

            $characterId = \max(\array_keys($characters)) + 1;
            $character   = $createCharacter($account, [
                'level'    => $level,
                'season'   => $seasonId,
                'created'  => (new \DateTime())->setTimestamp($from->getTimestamp() + (int) ($span * ($c === 0 ? rnd(0, 6) : rnd(20, 60)) / 100) + 3600 * rnd(16, 22)),
                'dead'     => $c > 0 && chance(50),
                'hardcore' => $c > 0,
            ]);

            // seasonal characters have a lower playtime
            $character['playtime'] = (int) ($character['playtime'] * 0.6);
            $character['xp']       = \min($character['xp'], $xpForLevel(60));
            $characters[$character['id']] = $character;
            $insertCharacter($character);

            insert('character_level_log', ['character' => $character['id'], 'level' => 1, 'playtime' => 0, 'datetime' => dt($character['created'])]);
            $seasonCharacters[$character['id']] = $seasonId;
        }

        $zones  = (int) \min($def[4], \round($def[4] * \min(1.0, $skill * 1.3)));
        $elites = (int) \min($def[5], \round($def[5] * $skill * rnd(70, 110) / 100));

        $reachedEnd = $zones === $def[4] && $mainLevel >= 60;
        $bossChance = $skill;
        $prevKilled = true;

        foreach ($bossIds as $b => $bossId) {
            if (!$reachedEnd || !$prevKilled) {
                $prevKilled = false;
                continue;
            }

            $attempts = rnd(2, 25);
            $killed   = chance((int) ($bossChance * 100));
            $date     = $killed
                ? (new \DateTime())->setTimestamp($from->getTimestamp() + (int) ($span * (0.55 + 0.4 * ($b + 1) / (\count($bossIds) + 1))))
                : null;

            insert('season_boss_kill', [
                'boss' => $bossId, 'account' => $account['id'], 'attempts' => $attempts,
                'killed' => $killed ? 1 : 0, 'datetime' => $date === null ? null : dt($date),
            ]);

            $bossChance *= 0.8;
            $prevKilled  = $killed;
        }

        $row = [
            'season' => $seasonId, 'account' => $account['id'], 'zones' => $zones, 'elites' => $elites,
            'events' => (int) (rnd(5, 120) * $account['activity']),
            'ladder_rating' => null, 'ladder_rank' => null, 'ladder_players' => null, 'ladder_wins' => null, 'ladder_losses' => null,
        ];

        if ($def[7] && chance(70)) {
            $share  = $current ? \max(0.1, $elapsed) : 1.0;
            $wins   = (int) \max(3, rnd(5, 140) * $share);
            $losses = (int) \max(3, rnd(5, 110) * $share);

            $row['ladder_rating'] = (int) \max(800, 1200 + ($wins - $losses) * rnd(4, 9) + rnd(-80, 120));
            $row['ladder_wins']   = $wins;
            $row['ladder_losses'] = $losses;
            ++$ladderPlayers;
        }

        $ladder[] = $row;
    }

    // ladder ranks within the dummy accounts, scaled to a bigger player base
    $ranked = \array_filter($ladder, fn ($r) => $r['ladder_rating'] !== null);
    \usort($ranked, fn ($a, $b) => $b['ladder_rating'] <=> $a['ladder_rating']);
    $rankOf = [];
    foreach ($ranked as $i => $r) {
        $rankOf[$r['account']] = $i + 1;
    }

    foreach ($ladder as $row) {
        if ($row['ladder_rating'] !== null) {
            $row['ladder_rank']    = $rankOf[$row['account']];
            $row['ladder_players'] = $ladderPlayers;
        }

        insert('season_progress', $row);
    }
}

/* ==========================================================================
   Character events
   ========================================================================== */

$eventCount = 0;
foreach ($characters as $cid => $c) {
    $events = [];

    // level ups
    foreach ($db->query('SELECT character_level_log_level, character_level_log_playtime, character_level_log_datetime FROM character_level_log WHERE character_level_log_character = ' . $cid) as $row) {
        $level = (int) $row[0];
        $events[] = $level === 1
            ? [$row[2], 'level', 'Character created', $c['class'] . ($c['season'] !== null ? ' · seasonal character' : ''), null, null]
            : [$row[2], 'level', 'Reached level ' . $level, 'after ' . \number_format((float) $row[1], 1) . ' h playtime', null, null];
    }

    // profession milestones
    foreach ($professions[$cid] ?? [] as $name => $professionLevel) {
        $milestones = [];
        for ($milestone = 25; $milestone <= $professionLevel; $milestone += 25) {
            $milestones[] = $milestone;
        }

        foreach ($milestones as $i => $milestone) {

            $date = (new \DateTime())->setTimestamp(
                $c['created']->getTimestamp() + (int) (($now->getTimestamp() - $c['created']->getTimestamp()) * ($i + 1) / (\count($milestones) + 1)) + rnd(0, 86400 * 5)
            );

            $events[] = [dt(\min($date, $now)), 'profession', \ucfirst($name) . ' level ' . $milestone, 'Profession level up', null, null];
        }
    }

    // encounters
    foreach ($participation[$cid] ?? [] as $p) {
        $events[] = [dt($p[0]), $p[1], $p[3], $p[4], null, $p[2]];
    }

    // loot
    foreach ($loot[$cid] ?? [] as $item) {
        $events[] = [dt($item[0]), 'loot', 'Obtained ' . $item[1], \ucfirst($rarityNames[$item[2]]) . ' item', $item[2], null];
    }

    // achievements are shown on the main character of the account
    if (\in_array($cid, $mains, true)) {
        foreach ($achievementTiers[$c['account']] ?? [] as $tier) {
            $events[] = [dt($tier[0]), 'achievement', 'Achievement: ' . $tier[1], 'Tier ' . ['I', 'II', 'III', 'IV', 'V'][$tier[2] - 1], null, null];
        }
    }

    foreach ($events as $event) {
        insert('character_event', [
            'character' => $cid, 'type' => $event[1], 'title' => $event[2], 'detail' => $event[3],
            'rarity' => $event[4], 'encounter' => $event[5], 'datetime' => $event[0],
        ]);
        ++$eventCount;
    }
}

/* ==========================================================================
   Feedback
   ========================================================================== */

$feedbackDefs = [
    ['2026-10-02 18:24', 'planned', 'Suggestion', 1002, 0.92, 'Add a transmog system for all armor types',
        "Right now the best armor often looks the worst. A transmog system would let us keep the stats of an item and use the look of another one we already collected.\n\nIdeally it works for all armor slots incl. shields and cloaks, and also lets us hide the helmet. The appearances could be unlocked when an item is looted, so there is a reason to farm old dungeons again.",
        [['2026-10-02', 'open'], ['2026-10-05', 'planned']],
        [1, '2026-10-05 10:12', 'We like this a lot and put it on the roadmap. Appearances will be unlocked on loot and stored per account. Weapons will follow later because of the animation sets.'],
        [[1001, '2026-10-02 19:02', 'Yes please! Also allow dyes on top of it.'], [1006, '2026-10-02 20:45', 'Should be free to change, otherwise people will just not use it.'], [1005, '2026-10-03 09:13', 'As long as PvP still shows the class silhouette clearly I am fine with it.'], [1, '2026-10-05 10:14', 'Changing the look will cost a small amount of gold, that is a gold sink we need anyway.']],
        110],
    ['2026-09-30 21:03', 'progress', 'Bug', 1004, 0.97, 'Pets sometimes get stuck on stairs',
        "Pets get stuck on the stairs in Ironhold and in the Moonwood Hollow dungeon. They only teleport back once you are around 60 meters away, which is a problem during boss fights because they stop attacking.\n\nSteps to reproduce: summon any pet, walk up the stairs next to the Ironhold bank, the pet stays at the first step.",
        [['2026-09-30', 'open'], ['2026-10-01', 'progress']],
        [1008, '2026-10-01 14:40', 'Thanks for the detailed steps. The navigation mesh is missing on some stairs, a fix is in testing.'],
        [[1001, '2026-09-30 21:30', 'Same with mounts in the Sunken Crypt.'], [1003, '2026-10-01 08:55', 'Happens on ladders too.']],
        60],
    ['2026-09-28 12:51', 'open', 'Suggestion', 1001, 0.94, 'Guild bank permissions per rank',
        "The guild bank currently only knows two states: full access or no access. We would like to set permissions per rank and per bank tab, e.g. members can deposit everywhere but only withdraw from the consumables tab.\n\nA daily withdraw limit per rank would also help a lot.",
        [['2026-09-28', 'open']], null,
        [[1002, '2026-09-28 14:10', 'A log of who took what would be great as well.']],
        55],
    ['2026-09-25 09:15', 'done', 'Website', 1005, 0.95, 'Show item rarity colors in the database',
        'In the item database all names are white. Using the rarity colors like in the game would make it much easier to scan the lists.',
        [['2026-09-25', 'open'], ['2026-09-26', 'planned'], ['2026-09-29', 'done']],
        [1, '2026-09-29 19:02', 'Done - the database, auction house and character equipment now use the rarity colors.'],
        [], 40],
    ['2026-09-21 22:37', 'open', 'Balance', 1003, 0.64, 'Berserker bleed damage too strong in 2v2',
        "The Berserker bleed stacks too fast in 2v2. With two Berserkers you can stack it to 10 within 6 seconds and healers can't keep up, even with the cleanse on cooldown.\n\nMaybe cap the stacks in arenas or reduce the duration.",
        [['2026-09-21', 'open']], null,
        [[1004, '2026-09-21 23:02', 'As a Druid I agree, it is impossible to outheal.'], [1001, '2026-09-22 07:40', 'It is fine in 3v3, so only change it for 2v2 please.'], [1007, '2026-09-22 12:15', 'Just play a Paladin and cleanse it.']],
        58],
    ['2026-09-18 17:44', 'closed', 'Suggestion', 1006, 0.69, 'Flying mounts in the open world',
        'Flying mounts would make traveling the big open world faster and more fun.',
        [['2026-09-18', 'open'], ['2026-09-22', 'closed']],
        [1, '2026-09-22 11:30', 'We decided against flying mounts. The world, world dungeons and open world PvP are built around ground travel. Faster ground mounts and more teleports are planned instead.'],
        [[1007, '2026-09-18 18:00', 'Would ruin the world PvP.'], [1004, '2026-09-23 10:20', 'Understandable decision, faster mounts sound good.']],
        75],
    ['2026-09-12 08:02', 'done', 'Bug', 1002, 0.95, 'Auction house sorting by price is reversed',
        'Sorting by price ascending shows the most expensive items first and the other way around.',
        [['2026-09-12', 'open'], ['2026-09-13', 'done']],
        [1008, '2026-09-13 16:20', 'Fixed with the last patch, thanks!'],
        [[1005, '2026-09-13 17:01', 'Works again, thanks for the quick fix.']],
        20],
    ['2026-09-08 20:11', 'open', 'Other', 1007, 0.66, 'Season character transfer after the season ends',
        'What happens with the seasonal characters at the end of a season? It would be great if they could be transferred to the normal servers incl. their items.',
        [['2026-09-08', 'open']], null, [], 18],
];

$voters = \array_keys($accounts);

foreach ($feedbackDefs as $def) {
    [$created, $state, $type, $author, $upShare, $title, $text, $history, $response, $comments, $voteCount] = $def;

    $feedbackId = insert('feedback', [
        'account'           => $author,
        'type'              => $type,
        'state'             => $state,
        'title'             => $title,
        'text'              => $text,
        'created'           => $created . ':00',
        'updated'           => \end($history)[0] . ' 12:00:00',
        'response'          => $response[2] ?? null,
        'response_account'  => $response[0] ?? null,
        'response_datetime' => isset($response[1]) ? $response[1] . ':00' : null,
    ]);

    foreach ($history as $step) {
        insert('feedback_state_log', ['feedback' => $feedbackId, 'state' => $step[1], 'datetime' => $step[0] . ' 12:00:00']);
    }

    foreach (\array_slice(mtShuffle($voters), 0, $voteCount) as $voter) {
        insert('feedback_vote', [
            'feedback' => $feedbackId, 'account' => $voter, 'value' => chance((int) ($upShare * 100)) ? 1 : -1,
            'datetime' => dt(randomDate(new \DateTime($created), $now)),
        ]);
    }

    foreach ($comments as $comment) {
        $commentId = insert('feedback_comment', ['feedback' => $feedbackId, 'account' => $comment[0], 'text' => $comment[2], 'created' => $comment[1] . ':00']);

        foreach (\array_slice(mtShuffle($voters), 0, rnd(2, 30)) as $voter) {
            insert('feedback_comment_vote', [
                'comment' => $commentId, 'account' => $voter, 'value' => chance(80) ? 1 : -1,
                'datetime' => dt(randomDate(new \DateTime($comment[1]), $now)),
            ]);
        }
    }
}

$db->commit();
$db->exec('VACUUM');

echo 'Seeded ' . \count($accounts) . ' accounts, ' . \count($characters) . ' characters, ' . \count($guilds) . ' guilds, '
    . (\array_sum($counts) + $pvpCount) . ' encounters, ' . $eventCount . " character events\n";
