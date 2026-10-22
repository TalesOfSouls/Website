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
 * CharacterLevelLog mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of CharacterLevelLog
 * @extends DataMapperFactory<T>
 */
class CharacterLevelLogMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'character_level_log_id'        => ['name' => 'character_level_log_id',       'type' => 'int',       'internal' => 'id'],
        'character_level_log_character' => ['name' => 'character_level_log_character', 'type' => 'int',       'internal' => 'character'],
        'character_level_log_level'     => ['name' => 'character_level_log_level',    'type' => 'int',       'internal' => 'level'],
        'character_level_log_playtime'  => ['name' => 'character_level_log_playtime', 'type' => 'float',     'internal' => 'playtime'],
        'character_level_log_datetime'  => ['name' => 'character_level_log_datetime', 'type' => 'DateTime',  'internal' => 'datetime'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = CharacterLevelLog::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'character_level_log';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'character_level_log_id';
}
