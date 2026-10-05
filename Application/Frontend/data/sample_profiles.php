<?php
/**
 * Sample profile data
 *
 * Used by the profile pages until the game servers fill the account/character/guild tables.
 * The ids start at 1001 so they don't collide with ids from the database.
 *
 * @todo Remove once real data exists
 */
declare(strict_types=1);

return [
    'players' => [
        1001 => ['name' => 'Aldric',  'since' => '2024-07-01', 'playtime' => 412, 'achievement_points' => 1850, 'pvp_kills' => 237, 'dungeons' => 54, 'characters' => [1001, 1002]],
        1002 => ['name' => 'Mirelle', 'since' => '2024-07-03', 'playtime' => 365, 'achievement_points' => 1620, 'pvp_kills' => 88,  'dungeons' => 71, 'characters' => [1003, 1004]],
        1003 => ['name' => 'Thorgar', 'since' => '2024-07-10', 'playtime' => 198, 'achievement_points' => 940,  'pvp_kills' => 512, 'dungeons' => 12, 'characters' => [1005]],
        1004 => ['name' => 'Sylvara', 'since' => '2024-08-02', 'playtime' => 150, 'achievement_points' => 720,  'pvp_kills' => 21,  'dungeons' => 33, 'characters' => [1006, 1007]],
        1005 => ['name' => 'Corvin',  'since' => '2024-08-15', 'playtime' => 87,  'achievement_points' => 410,  'pvp_kills' => 9,   'dungeons' => 18, 'characters' => [1008]],
    ],

    'characters' => [
        1001 => ['name' => 'Aldric',   'player' => 1001, 'class' => 'Paladin',     'level' => 99, 'server' => 'Test Server 1', 'guild' => 1001, 'playtime' => 310, 'rank' => 1,  'item_level' => 2140],
        1002 => ['name' => 'Brannoc',  'player' => 1001, 'class' => 'Knight',      'level' => 41, 'server' => 'Test Server 1', 'guild' => 1001, 'playtime' => 102, 'rank' => 58, 'item_level' => 820],
        1003 => ['name' => 'Mirelle',  'player' => 1002, 'class' => 'Mage',        'level' => 98, 'server' => 'Test Server 2', 'guild' => 1002, 'playtime' => 290, 'rank' => 2,  'item_level' => 2085],
        1004 => ['name' => 'Ysolde',   'player' => 1002, 'class' => 'Priest',      'level' => 63, 'server' => 'Test Server 2', 'guild' => 1002, 'playtime' => 75,  'rank' => 31, 'item_level' => 1290],
        1005 => ['name' => 'Thorgar',  'player' => 1003, 'class' => 'Berserker',   'level' => 96, 'server' => 'Test Server 1', 'guild' => 1003, 'playtime' => 198, 'rank' => 3,  'item_level' => 1990],
        1006 => ['name' => 'Sylvara',  'player' => 1004, 'class' => 'Druid',       'level' => 95, 'server' => 'Test Server 2', 'guild' => 1002, 'playtime' => 120, 'rank' => 4,  'item_level' => 1940],
        1007 => ['name' => 'Fenrik',   'player' => 1004, 'class' => 'Hunter',      'level' => 27, 'server' => 'Test Server 2', 'guild' => 0,    'playtime' => 30,  'rank' => 77, 'item_level' => 510],
        1008 => ['name' => 'Corvin',   'player' => 1005, 'class' => 'Necromancer', 'level' => 88, 'server' => 'Test Server 1', 'guild' => 1001, 'playtime' => 87,  'rank' => 8,  'item_level' => 1720],
    ],

    'guilds' => [
        1001 => ['name' => 'Ashen Covenant',   'tag' => 'ASH', 'server' => 'Test Server 1', 'focus' => 'Raids, PvP',      'recruiting' => true,  'founded' => '2024-07-05', 'raid_rank' => 1,  'description' => 'A raiding guild with a soft spot for world bosses.', 'members' => [1001 => 'Founder', 1008 => 'Moderator', 1002 => 'Member']],
        1002 => ['name' => 'Moonlit Vanguard', 'tag' => 'MLV', 'server' => 'Test Server 2', 'focus' => 'Dungeons, PvE',   'recruiting' => true,  'founded' => '2024-07-12', 'raid_rank' => 3,  'description' => 'Relaxed PvE guild, timed dungeons every evening.',  'members' => [1003 => 'Founder', 1006 => 'Moderator', 1004 => 'Member']],
        1003 => ['name' => 'Iron Oath',        'tag' => 'IRON', 'server' => 'Test Server 1', 'focus' => 'World vs. World', 'recruiting' => false, 'founded' => '2024-08-01', 'raid_rank' => 12, 'description' => 'Siege specialists. We hold the line.',             'members' => [1005 => 'Founder']],
    ],
];
