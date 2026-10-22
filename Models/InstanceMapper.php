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
 * Instance mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Instance
 * @extends DataMapperFactory<T>
 */
class InstanceMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'instance_id'       => ['name' => 'instance_id',      'type' => 'int',       'internal' => 'id'],
        'instance_type'     => ['name' => 'instance_type',    'type' => 'string',    'internal' => 'type'],
        'instance_name'     => ['name' => 'instance_name',    'type' => 'string',    'internal' => 'name'],
        'instance_location' => ['name' => 'instance_location', 'type' => 'string',    'internal' => 'location'],
        'instance_size'     => ['name' => 'instance_size',    'type' => 'int',       'internal' => 'size'],
        'instance_pvp_mode' => ['name' => 'instance_pvp_mode', 'type' => 'int',       'internal' => 'pvpMode'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'pvpMode' => [
            'mapper'   => PvpModeMapper::class,
            'external' => 'instance_pvp_mode',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'bosses' => [
            'mapper'   => InstanceBossMapper::class,
            'table'    => 'instance_boss',
            'self'     => 'instance_boss_instance',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Instance::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'instance';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'instance_id';
}
