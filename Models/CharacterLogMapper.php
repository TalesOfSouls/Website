<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Model
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Models;

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * CharacterLog mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of CharacterLog
 * @extends DataMapperFactory<T>
 */
class CharacterLogMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'character_log_id'               => ['name' => 'character_log_id',              'type' => 'int',       'internal' => 'id'],
        'character_log_character'        => ['name' => 'character_log_character',       'type' => 'int',       'internal' => 'character'],
        'character_log_gold'             => ['name' => 'character_log_gold',            'type' => 'int',       'internal' => 'gold'],
        'character_log_playtime'         => ['name' => 'character_log_playtime',        'type' => 'int',       'internal' => 'playtime'],
        'character_log_chat'             => ['name' => 'character_log_chat',            'type' => 'int',       'internal' => 'chat'],
        'character_log_mobs_killed'      => ['name' => 'character_log_mobs_killed',     'type' => 'int',       'internal' => 'mobsKilled'],
        'character_log_players_killed'   => ['name' => 'character_log_players_killed',  'type' => 'int',       'internal' => 'playersKilled'],
        'character_log_dungeons'         => ['name' => 'character_log_dungeons',        'type' => 'int',       'internal' => 'dungeons'],
        'character_log_dungeons_success' => ['name' => 'character_log_dungeons_success', 'type' => 'int',       'internal' => 'dungeonsSuccess'],
        'character_log_dungeons_quit'    => ['name' => 'character_log_dungeons_quit',   'type' => 'int',       'internal' => 'dungeonsQuit'],
        'character_log_raids'            => ['name' => 'character_log_raids',           'type' => 'int',       'internal' => 'raids'],
        'character_log_raids_success'    => ['name' => 'character_log_raids_success',   'type' => 'int',       'internal' => 'raidsSuccess'],
        'character_log_raids_quit'       => ['name' => 'character_log_raids_quit',      'type' => 'int',       'internal' => 'raidsQuit'],
        'character_log_pvp'              => ['name' => 'character_log_pvp',             'type' => 'int',       'internal' => 'pvp'],
        'character_log_pvp_quit'         => ['name' => 'character_log_pvp_quit',        'type' => 'int',       'internal' => 'pvpQuit'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = CharacterLog::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'character_log';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'character_log_id';
}
