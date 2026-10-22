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
 * Season mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Season
 * @extends DataMapperFactory<T>
 */
class SeasonMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'season_id'      => ['name' => 'season_id',     'type' => 'int',       'internal' => 'id'],
        'season_name'    => ['name' => 'season_name',   'type' => 'string',    'internal' => 'name'],
        'season_start'   => ['name' => 'season_start',  'type' => 'DateTime',  'internal' => 'start'],
        'season_end'     => ['name' => 'season_end',    'type' => 'DateTime',  'internal' => 'end'],
        'season_dungeon' => ['name' => 'season_dungeon', 'type' => 'string',    'internal' => 'dungeon'],
        'season_zones'   => ['name' => 'season_zones',  'type' => 'int',       'internal' => 'zones'],
        'season_elites'  => ['name' => 'season_elites', 'type' => 'int',       'internal' => 'elites'],
        'season_ladder'  => ['name' => 'season_ladder', 'type' => 'bool',      'internal' => 'ladder'],
        'season_event'   => ['name' => 'season_event',  'type' => 'string',    'internal' => 'event'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'bosses' => [
            'mapper'   => SeasonBossMapper::class,
            'table'    => 'season_boss',
            'self'     => 'season_boss_season',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Season::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'season';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'season_id';
}
