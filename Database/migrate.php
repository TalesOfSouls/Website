<?php
/**
 * Tales of Souls
 *
 * Website database migration
 *
 * Adds the tables and columns the website needs to TalesOfSouls.db.
 * The script can be run multiple times, existing tables/columns are not touched.
 *
 * Usage: php Database/migrate.php [path/to/TalesOfSouls.db]
 *
 * PHP Version 8.2
 *
 * @package   Database
 * @version   1.0.0
 */
declare(strict_types=1);

$path = $argv[1] ?? __DIR__ . '/../TalesOfSouls.db';
$db   = new \PDO('sqlite:' . $path);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA foreign_keys = OFF');

/**
 * Existing columns of a table
 */
$columns = function (string $table) use ($db) : array {
    return \array_column($db->query('PRAGMA table_info("' . $table . '")')->fetchAll(\PDO::FETCH_ASSOC), 'name');
};

$tableExists = function (string $table) use ($db) : bool {
    $stmt = $db->prepare('SELECT 1 FROM sqlite_master WHERE type = \'table\' AND name = ?');
    $stmt->execute([$table]);

    return $stmt->fetchColumn() !== false;
};

$addColumns = function (string $table, array $definitions) use ($db, $columns) : void {
    $existing = $columns($table);

    foreach ($definitions as $name => $definition) {
        if (!\in_array($name, $existing, true)) {
            $db->exec('ALTER TABLE "' . $table . '" ADD COLUMN "' . $name . '" ' . $definition);
            echo "  + {$table}.{$name}\n";
        }
    }
};

$db->beginTransaction();

/* ==========================================================================
   Skills
   The original skill table only had an id. The skill list is maintained in Database/skills.sql
   ========================================================================== */

if (!\in_array('skill_name', $columns('skill'), true)) {
    $db->exec('DROP TABLE IF EXISTS "skill"');
    $db->exec('CREATE TABLE "skill" (
        "skill_id"   INTEGER,
        "skill_name" TEXT,
        "skill_note" TEXT,
        PRIMARY KEY("skill_id" AUTOINCREMENT)
    )');
    $db->exec((string) \file_get_contents(__DIR__ . '/skills.sql'));
    echo "  ~ skill (recreated incl. skills.sql)\n";
}

// cooldown in seconds (0 = no cooldown), kind: damage|heal|utility, target: single|aoe
$addColumns('skill', [
    'skill_cooldown' => 'INTEGER',
    'skill_kind'     => 'TEXT',
    'skill_target'   => 'TEXT',
]);

/* ==========================================================================
   Columns of existing tables
   ========================================================================== */

$addColumns('account', [
    'account_created'   => 'TEXT',
    'account_lastlogin' => 'TEXT',
    'account_status'    => 'INTEGER DEFAULT 1', // 0 = inactive, 1 = active, 2 = banned
    'account_role'      => 'INTEGER DEFAULT 0', // 0 = player, 1 = developer, 2 = admin
]);

$addColumns('character', [
    'character_race'       => 'INTEGER REFERENCES "race"("race_id")',
    'character_created'    => 'TEXT',
    'character_season'     => 'INTEGER', // seasonal characters (NULL = normal character)
    'character_hardcore'   => 'INTEGER DEFAULT 0',
    'character_dead'       => 'INTEGER DEFAULT 0',
    'character_item_level' => 'INTEGER DEFAULT 0',
]);

$addColumns('character_equipment', [
    'character_equipment_artifact' => 'INTEGER',
]);

$addColumns('guild', [
    'guild_description' => 'TEXT',
    'guild_created'     => 'TEXT', // NULL for reserved guild names
    'guild_recruiting'  => 'INTEGER DEFAULT 0',
]);

$addColumns('guild_member', [
    'guild_member_guild'  => 'INTEGER REFERENCES "guild"("guild_id")',
    'guild_member_joined' => 'TEXT',
]);

// Individual (rolled) items, e.g. equipped items
$addColumns('item_individual', [
    'item_individual_equipment_type' => 'INTEGER REFERENCES "equipment_type"("equipment_type_id")',
    'item_individual_name'           => 'TEXT',
    'item_individual_rarity'         => 'INTEGER REFERENCES "item_rarity"("item_rarity_id")',
    'item_individual_level'          => 'INTEGER',
    'item_individual_character'      => 'INTEGER REFERENCES "character"("character_id")',
    'item_individual_created'        => 'TEXT',
]);

/* ==========================================================================
   New tables
   ========================================================================== */

$tables = [
    'achievement_category' => '
        "achievement_category_id"   INTEGER,
        "achievement_category_name" TEXT,
        PRIMARY KEY("achievement_category_id" AUTOINCREMENT)',

    // Unlocked achievement tiers (account_achievement holds the current progress)
    'account_achievement_tier' => '
        "account_achievement_tier_id"       INTEGER,
        "account_achievement_tier_account"  INTEGER,
        "account_achievement_tier_type"     INTEGER,
        "account_achievement_tier_tier"     INTEGER,
        "account_achievement_tier_datetime" TEXT,
        PRIMARY KEY("account_achievement_tier_id" AUTOINCREMENT),
        FOREIGN KEY("account_achievement_tier_account") REFERENCES "account"("account_id"),
        FOREIGN KEY("account_achievement_tier_type") REFERENCES "achievement_type"("achievement_type_id")',

    // Event log of a character (level up, dungeon clear, loot, ...)
    'character_event' => '
        "character_event_id"        INTEGER,
        "character_event_character" INTEGER,
        "character_event_type"      TEXT,
        "character_event_title"     TEXT,
        "character_event_detail"    TEXT,
        "character_event_rarity"    INTEGER,
        "character_event_encounter" INTEGER,
        "character_event_datetime"  TEXT,
        PRIMARY KEY("character_event_id" AUTOINCREMENT),
        FOREIGN KEY("character_event_character") REFERENCES "character"("character_id"),
        FOREIGN KEY("character_event_encounter") REFERENCES "encounter"("encounter_id")',

    // Level ups incl. playtime (hours) at that moment
    'character_level_log' => '
        "character_level_log_id"        INTEGER,
        "character_level_log_character" INTEGER,
        "character_level_log_level"     INTEGER,
        "character_level_log_playtime"  REAL,
        "character_level_log_datetime"  TEXT,
        PRIMARY KEY("character_level_log_id" AUTOINCREMENT),
        FOREIGN KEY("character_level_log_character") REFERENCES "character"("character_id")',

    // Dungeons, raids, world bosses and PvP maps
    'instance' => '
        "instance_id"           INTEGER,
        "instance_type"         TEXT,
        "instance_name"         TEXT,
        "instance_location"     TEXT,
        "instance_size"         INTEGER,
        "instance_pvp_mode"     INTEGER,
        PRIMARY KEY("instance_id" AUTOINCREMENT),
        FOREIGN KEY("instance_pvp_mode") REFERENCES "pvp_mode"("pvp_mode_id")',

    'instance_boss' => '
        "instance_boss_id"       INTEGER,
        "instance_boss_instance" INTEGER,
        "instance_boss_name"     TEXT,
        "instance_boss_order"    INTEGER,
        PRIMARY KEY("instance_boss_id" AUTOINCREMENT),
        FOREIGN KEY("instance_boss_instance") REFERENCES "instance"("instance_id")',

    'pvp_mode' => '
        "pvp_mode_id"     INTEGER,
        "pvp_mode_name"   TEXT,
        "pvp_mode_size"   INTEGER,
        "pvp_mode_target" INTEGER,
        "pvp_mode_unit"   TEXT,
        PRIMARY KEY("pvp_mode_id" AUTOINCREMENT)',

    // A dungeon run, raid, world boss fight or PvP match
    'encounter' => '
        "encounter_id"         INTEGER,
        "encounter_type"       TEXT,
        "encounter_instance"   INTEGER,
        "encounter_difficulty" TEXT,
        "encounter_start"      TEXT,
        "encounter_duration"   INTEGER,
        "encounter_success"    INTEGER,
        "encounter_winner"     INTEGER,
        "encounter_score1"     INTEGER,
        "encounter_score2"     INTEGER,
        PRIMARY KEY("encounter_id" AUTOINCREMENT),
        FOREIGN KEY("encounter_instance") REFERENCES "instance"("instance_id")',

    'encounter_member' => '
        "encounter_member_id"               INTEGER,
        "encounter_member_encounter"        INTEGER,
        "encounter_member_character"        INTEGER,
        "encounter_member_team"             INTEGER DEFAULT 1,
        "encounter_member_role"             TEXT,
        "encounter_member_damage"           INTEGER,
        "encounter_member_damage_single"    INTEGER,
        "encounter_member_damage_aoe"       INTEGER,
        "encounter_member_healing"          INTEGER,
        "encounter_member_healing_single"   INTEGER,
        "encounter_member_healing_aoe"      INTEGER,
        "encounter_member_healing_received" INTEGER,
        "encounter_member_damage_taken"     INTEGER,
        "encounter_member_deaths"           INTEGER,
        "encounter_member_kills"            INTEGER,
        "encounter_member_assists"          INTEGER,
        "encounter_member_objectives"       INTEGER,
        "encounter_member_casts"            INTEGER,
        "encounter_member_apm"              REAL,
        "encounter_member_cooldown_usage"   INTEGER,
        "encounter_member_active"           INTEGER,
        "encounter_member_rating_before"    INTEGER,
        "encounter_member_rating_change"    INTEGER,
        PRIMARY KEY("encounter_member_id" AUTOINCREMENT),
        FOREIGN KEY("encounter_member_encounter") REFERENCES "encounter"("encounter_id"),
        FOREIGN KEY("encounter_member_character") REFERENCES "character"("character_id")',

    'encounter_member_skill' => '
        "encounter_member_skill_id"       INTEGER,
        "encounter_member_skill_member"   INTEGER,
        "encounter_member_skill_skill"    INTEGER,
        "encounter_member_skill_casts"    INTEGER,
        "encounter_member_skill_possible" INTEGER,
        "encounter_member_skill_amount"   INTEGER,
        PRIMARY KEY("encounter_member_skill_id" AUTOINCREMENT),
        FOREIGN KEY("encounter_member_skill_member") REFERENCES "encounter_member"("encounter_member_id"),
        FOREIGN KEY("encounter_member_skill_skill") REFERENCES "skill"("skill_id")',

    'encounter_boss' => '
        "encounter_boss_id"        INTEGER,
        "encounter_boss_encounter" INTEGER,
        "encounter_boss_boss"      INTEGER,
        "encounter_boss_killed"    INTEGER,
        PRIMARY KEY("encounter_boss_id" AUTOINCREMENT),
        FOREIGN KEY("encounter_boss_encounter") REFERENCES "encounter"("encounter_id"),
        FOREIGN KEY("encounter_boss_boss") REFERENCES "instance_boss"("instance_boss_id")',

    'encounter_attempt' => '
        "encounter_attempt_id"       INTEGER,
        "encounter_attempt_boss"     INTEGER,
        "encounter_attempt_no"       INTEGER,
        "encounter_attempt_duration" INTEGER,
        "encounter_attempt_hp_left"  INTEGER,
        "encounter_attempt_kill"     INTEGER,
        PRIMARY KEY("encounter_attempt_id" AUTOINCREMENT),
        FOREIGN KEY("encounter_attempt_boss") REFERENCES "encounter_boss"("encounter_boss_id")',

    // Statistics of the kill attempt (or last attempt) per boss and member
    'encounter_boss_stat' => '
        "encounter_boss_stat_id"       INTEGER,
        "encounter_boss_stat_boss"     INTEGER,
        "encounter_boss_stat_member"   INTEGER,
        "encounter_boss_stat_damage"   INTEGER,
        "encounter_boss_stat_healing"  INTEGER,
        "encounter_boss_stat_received" INTEGER,
        "encounter_boss_stat_taken"    INTEGER,
        PRIMARY KEY("encounter_boss_stat_id" AUTOINCREMENT),
        FOREIGN KEY("encounter_boss_stat_boss") REFERENCES "encounter_boss"("encounter_boss_id"),
        FOREIGN KEY("encounter_boss_stat_member") REFERENCES "encounter_member"("encounter_member_id")',

    // PvP score progression
    'encounter_score' => '
        "encounter_score_id"        INTEGER,
        "encounter_score_encounter" INTEGER,
        "encounter_score_time"      INTEGER,
        "encounter_score_score1"    INTEGER,
        "encounter_score_score2"    INTEGER,
        PRIMARY KEY("encounter_score_id" AUTOINCREMENT),
        FOREIGN KEY("encounter_score_encounter") REFERENCES "encounter"("encounter_id")',

    'season' => '
        "season_id"      INTEGER,
        "season_name"    TEXT,
        "season_start"   TEXT,
        "season_end"     TEXT,
        "season_dungeon" TEXT,
        "season_zones"   INTEGER,
        "season_elites"  INTEGER,
        "season_ladder"  INTEGER DEFAULT 0,
        "season_event"   TEXT,
        PRIMARY KEY("season_id" AUTOINCREMENT)',

    'season_boss' => '
        "season_boss_id"     INTEGER,
        "season_boss_season" INTEGER,
        "season_boss_name"   TEXT,
        "season_boss_order"  INTEGER,
        PRIMARY KEY("season_boss_id" AUTOINCREMENT),
        FOREIGN KEY("season_boss_season") REFERENCES "season"("season_id")',

    'season_progress' => '
        "season_progress_id"             INTEGER,
        "season_progress_season"         INTEGER,
        "season_progress_account"        INTEGER,
        "season_progress_zones"          INTEGER DEFAULT 0,
        "season_progress_elites"         INTEGER DEFAULT 0,
        "season_progress_events"         INTEGER DEFAULT 0,
        "season_progress_ladder_rating"  INTEGER,
        "season_progress_ladder_rank"    INTEGER,
        "season_progress_ladder_players" INTEGER,
        "season_progress_ladder_wins"    INTEGER,
        "season_progress_ladder_losses"  INTEGER,
        PRIMARY KEY("season_progress_id" AUTOINCREMENT),
        FOREIGN KEY("season_progress_season") REFERENCES "season"("season_id"),
        FOREIGN KEY("season_progress_account") REFERENCES "account"("account_id")',

    'season_boss_kill' => '
        "season_boss_kill_id"       INTEGER,
        "season_boss_kill_boss"     INTEGER,
        "season_boss_kill_account"  INTEGER,
        "season_boss_kill_attempts" INTEGER,
        "season_boss_kill_killed"   INTEGER DEFAULT 0,
        "season_boss_kill_datetime" TEXT,
        PRIMARY KEY("season_boss_kill_id" AUTOINCREMENT),
        FOREIGN KEY("season_boss_kill_boss") REFERENCES "season_boss"("season_boss_id"),
        FOREIGN KEY("season_boss_kill_account") REFERENCES "account"("account_id")',

    'feedback' => '
        "feedback_id"                INTEGER,
        "feedback_account"           INTEGER,
        "feedback_type"              TEXT,
        "feedback_state"             TEXT DEFAULT \'open\',
        "feedback_title"             TEXT,
        "feedback_text"              TEXT,
        "feedback_created"           TEXT,
        "feedback_updated"           TEXT,
        "feedback_response"          TEXT,
        "feedback_response_account"  INTEGER,
        "feedback_response_datetime" TEXT,
        PRIMARY KEY("feedback_id" AUTOINCREMENT),
        FOREIGN KEY("feedback_account") REFERENCES "account"("account_id"),
        FOREIGN KEY("feedback_response_account") REFERENCES "account"("account_id")',

    'feedback_state_log' => '
        "feedback_state_log_id"       INTEGER,
        "feedback_state_log_feedback" INTEGER,
        "feedback_state_log_state"    TEXT,
        "feedback_state_log_datetime" TEXT,
        PRIMARY KEY("feedback_state_log_id" AUTOINCREMENT),
        FOREIGN KEY("feedback_state_log_feedback") REFERENCES "feedback"("feedback_id")',

    // One vote per account (+1 / -1)
    'feedback_vote' => '
        "feedback_vote_id"       INTEGER,
        "feedback_vote_feedback" INTEGER,
        "feedback_vote_account"  INTEGER,
        "feedback_vote_value"    INTEGER,
        "feedback_vote_datetime" TEXT,
        PRIMARY KEY("feedback_vote_id" AUTOINCREMENT),
        UNIQUE("feedback_vote_feedback", "feedback_vote_account"),
        FOREIGN KEY("feedback_vote_feedback") REFERENCES "feedback"("feedback_id"),
        FOREIGN KEY("feedback_vote_account") REFERENCES "account"("account_id")',

    'feedback_comment' => '
        "feedback_comment_id"       INTEGER,
        "feedback_comment_feedback" INTEGER,
        "feedback_comment_account"  INTEGER,
        "feedback_comment_text"     TEXT,
        "feedback_comment_created"  TEXT,
        PRIMARY KEY("feedback_comment_id" AUTOINCREMENT),
        FOREIGN KEY("feedback_comment_feedback") REFERENCES "feedback"("feedback_id"),
        FOREIGN KEY("feedback_comment_account") REFERENCES "account"("account_id")',

    'feedback_comment_vote' => '
        "feedback_comment_vote_id"       INTEGER,
        "feedback_comment_vote_comment"  INTEGER,
        "feedback_comment_vote_account"  INTEGER,
        "feedback_comment_vote_value"    INTEGER,
        "feedback_comment_vote_datetime" TEXT,
        PRIMARY KEY("feedback_comment_vote_id" AUTOINCREMENT),
        UNIQUE("feedback_comment_vote_comment", "feedback_comment_vote_account"),
        FOREIGN KEY("feedback_comment_vote_comment") REFERENCES "feedback_comment"("feedback_comment_id"),
        FOREIGN KEY("feedback_comment_vote_account") REFERENCES "account"("account_id")',

    'contact_message' => '
        "contact_message_id"       INTEGER,
        "contact_message_account"  INTEGER,
        "contact_message_name"     TEXT,
        "contact_message_email"    TEXT,
        "contact_message_subject"  TEXT,
        "contact_message_text"     TEXT,
        "contact_message_created"  TEXT,
        PRIMARY KEY("contact_message_id" AUTOINCREMENT),
        FOREIGN KEY("contact_message_account") REFERENCES "account"("account_id")',

    // News posts (markdown)
    'news' => '
        "news_id"        INTEGER,
        "news_slug"      TEXT UNIQUE,
        "news_title"     TEXT,
        "news_summary"   TEXT,
        "news_image"     TEXT,
        "news_content"   TEXT,
        "news_account"   INTEGER,
        "news_status"    TEXT DEFAULT \'draft\',
        "news_published" TEXT,
        "news_created"   TEXT,
        "news_updated"   TEXT,
        PRIMARY KEY("news_id" AUTOINCREMENT),
        FOREIGN KEY("news_account") REFERENCES "account"("account_id")',

    // Wiki articles (markdown/html), module = optional data block rendered below the article
    'wiki_page' => '
        "wiki_page_id"        INTEGER,
        "wiki_page_slug"      TEXT UNIQUE,
        "wiki_page_title"     TEXT,
        "wiki_page_nav_title" TEXT,
        "wiki_page_content"   TEXT,
        "wiki_page_module"    TEXT,
        "wiki_page_nav_order" INTEGER,
        "wiki_page_account"   INTEGER,
        "wiki_page_updated"   TEXT,
        PRIMARY KEY("wiki_page_id" AUTOINCREMENT),
        FOREIGN KEY("wiki_page_account") REFERENCES "account"("account_id")',

    // Development roadmap
    'milestone' => '
        "milestone_id"          INTEGER,
        "milestone_title"       TEXT,
        "milestone_description" TEXT,
        "milestone_state"       TEXT DEFAULT \'planned\',
        "milestone_when"        TEXT,
        "milestone_order"       INTEGER DEFAULT 0,
        PRIMARY KEY("milestone_id" AUTOINCREMENT)',

    // Development progress per area (0 - 100)
    'development_area' => '
        "development_area_id"       INTEGER,
        "development_area_name"     TEXT,
        "development_area_progress" INTEGER DEFAULT 0,
        "development_area_order"    INTEGER DEFAULT 0,
        PRIMARY KEY("development_area_id" AUTOINCREMENT)',
];

foreach ($tables as $table => $definition) {
    if (!$tableExists($table)) {
        $db->exec('CREATE TABLE "' . $table . '" (' . $definition . ')');
        echo "  + table {$table}\n";
    }
}


/* ==========================================================================
   Content imports (only into empty tables)
   ========================================================================== */

$isEmpty = fn (string $table) : bool => (int) $db->query('SELECT COUNT(*) FROM "' . $table . '"')->fetchColumn() === 0;

// News: the former markdown files (Application/Frontend/data/news/YYYY/YYYY_MM_DD_slug.md)
if ($isEmpty('news')) {
    $stmt = $db->prepare('INSERT INTO news (news_slug, news_title, news_summary, news_image, news_content, news_account, news_status, news_published, news_created, news_updated) VALUES (?, ?, ?, ?, ?, 1, \'published\', ?, ?, ?)');

    foreach (\glob(__DIR__ . '/../Application/Frontend/data/news/*/*.md') ?: [] as $file) {
        $name = \basename($file, '.md');
        if (\preg_match('/^(\d{4})_(\d{2})_(\d{2})_(.+)$/', $name, $match) !== 1) {
            continue;
        }

        $raw   = \trim((string) \file_get_contents($file));
        $raw   = \str_replace(['](../img/', '"../img/'], ['](/Application/Frontend/data/news/img/', '"/Application/Frontend/data/news/img/'], $raw);
        $title = \ucfirst(\str_replace('_', ' ', $match[4]));

        if (\preg_match('/^#\s+(.+)$/m', $raw, $heading) === 1) {
            $title = \trim($heading[1]);
            $raw   = \trim(\preg_replace('/^#\s+.+$/m', '', $raw, 1) ?? $raw);
        }

        $summary = '';
        foreach (\preg_split('/\R\R+/', $raw) ?: [] as $block) {
            $block = \trim($block);
            if ($block !== '' && !\in_array($block[0], ['#', '!', '*', '-'], true)) {
                $summary = \mb_strimwidth(\strip_tags($block), 0, 220, '...');
                break;
            }
        }

        $image = \preg_match('/!\[[^\]]*\]\(([^)]+)\)/', $raw, $img) === 1 ? $img[1] : '';
        $date  = $match[1] . '-' . $match[2] . '-' . $match[3] . ' 12:00:00';

        $stmt->execute([\strtolower(\str_replace('_', '-', $name)), $title, $summary, $image, $raw, $date, $date, $date]);
        echo "  + news {$name}\n";
    }
}

// Wiki: the former wiki templates (see wiki_pages.json)
if ($isEmpty('wiki_page')) {
    $stmt = $db->prepare('INSERT INTO wiki_page (wiki_page_slug, wiki_page_title, wiki_page_nav_title, wiki_page_content, wiki_page_module, wiki_page_nav_order, wiki_page_account, wiki_page_updated) VALUES (?, ?, ?, ?, ?, ?, 1, ?)');

    foreach (\json_decode((string) \file_get_contents(__DIR__ . '/wiki_pages.json'), true) ?? [] as $page) {
        $stmt->execute([$page['slug'], $page['title'], $page['nav_title'], $page['content'], $page['module'], $page['nav_order'], '2026-10-01 12:00:00']);
    }

    echo "  + wiki pages\n";
}

// Development roadmap
if ($isEmpty('milestone')) {
    $stmt = $db->prepare('INSERT INTO milestone (milestone_title, milestone_description, milestone_state, milestone_when, milestone_order) VALUES (?, ?, ?, ?, ?)');

    foreach ([
        ['Game documentation', 'Concept, story and game systems are documented.', 'done', 'Jul 2024'],
        ['First render', 'The engine opens a window and renders its first frame.', 'done', 'Jul 2024'],
        ['Program structure', 'Hot reloadable game code, renderer abstraction, memory pools.', 'done', 'Jul 2024'],
        ['Website', 'News, wiki, database, ladders and community pages.', 'progress', '2026'],
        ['Beta 0.1.0 Dev Stream', 'A live look at the first playable build.', 'planned', 'TBA'],
        ['Beta 0.1.0 Release', 'First closed beta for supporters.', 'planned', 'TBA'],
        ['Release Dev Stream', 'Final preparations for the launch.', 'planned', 'TBA'],
        ['Reset & Release 1.0.0', 'Progress reset and official release.', 'planned', 'TBA'],
    ] as $i => $milestone) {
        $stmt->execute([$milestone[0], $milestone[1], $milestone[2], $milestone[3], ($i + 1) * 10]);
    }

    echo "  + milestones\n";
}

if ($isEmpty('development_area')) {
    $stmt = $db->prepare('INSERT INTO development_area (development_area_name, development_area_progress, development_area_order) VALUES (?, ?, ?)');

    foreach ([['Engine core', 45], ['Rendering', 30], ['Networking', 15], ['Game systems', 20], ['World & content', 10], ['Website', 40]] as $i => $area) {
        $stmt->execute([$area[0], $area[1], ($i + 1) * 10]);
    }

    echo "  + development areas\n";
}

/* ==========================================================================
   Indices
   ========================================================================== */

$indices = [
    'idx_character_account'          => '"character"("character_account")',
    'idx_character_guild'            => '"character"("character_guild")',
    'idx_character_event_character'  => '"character_event"("character_event_character", "character_event_datetime")',
    'idx_character_level_character'  => '"character_level_log"("character_level_log_character")',
    'idx_encounter_member_character' => '"encounter_member"("encounter_member_character")',
    'idx_encounter_member_encounter' => '"encounter_member"("encounter_member_encounter")',
    'idx_encounter_skill_member'     => '"encounter_member_skill"("encounter_member_skill_member")',
    'idx_encounter_boss_encounter'   => '"encounter_boss"("encounter_boss_encounter")',
    'idx_encounter_attempt_boss'     => '"encounter_attempt"("encounter_attempt_boss")',
    'idx_encounter_boss_stat_boss'   => '"encounter_boss_stat"("encounter_boss_stat_boss")',
    'idx_encounter_score_encounter'  => '"encounter_score"("encounter_score_encounter")',
    'idx_achievement_tier_account'   => '"account_achievement_tier"("account_achievement_tier_account")',
    'idx_account_achievement'        => '"account_achievement"("account_achievement_account")',
    'idx_season_progress_account'    => '"season_progress"("season_progress_account")',
    'idx_season_boss_kill_account'   => '"season_boss_kill"("season_boss_kill_account")',
    'idx_feedback_comment_feedback'  => '"feedback_comment"("feedback_comment_feedback")',
    'idx_guild_member_guild'         => '"guild_member"("guild_member_guild")',
    'idx_account_name'               => '"account"("account_name")',
];

foreach ($indices as $name => $on) {
    $db->exec('CREATE INDEX IF NOT EXISTS "' . $name . '" ON ' . $on);
}

$db->commit();

echo "Migration done: {$path}\n";
