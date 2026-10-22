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
 * InstanceBoss mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of InstanceBoss
 * @extends DataMapperFactory<T>
 */
class InstanceBossMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'instance_boss_id'       => ['name' => 'instance_boss_id',      'type' => 'int',       'internal' => 'id'],
        'instance_boss_instance' => ['name' => 'instance_boss_instance', 'type' => 'int',       'internal' => 'instance'],
        'instance_boss_name'     => ['name' => 'instance_boss_name',    'type' => 'string',    'internal' => 'name'],
        'instance_boss_order'    => ['name' => 'instance_boss_order',   'type' => 'int',       'internal' => 'order'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = InstanceBoss::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'instance_boss';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'instance_boss_id';
}
