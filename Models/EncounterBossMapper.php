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
 * EncounterBoss mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of EncounterBoss
 * @extends DataMapperFactory<T>
 */
class EncounterBossMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'encounter_boss_id'        => ['name' => 'encounter_boss_id',       'type' => 'int',       'internal' => 'id'],
        'encounter_boss_encounter' => ['name' => 'encounter_boss_encounter', 'type' => 'int',       'internal' => 'encounter'],
        'encounter_boss_boss'      => ['name' => 'encounter_boss_boss',     'type' => 'int',       'internal' => 'boss'],
        'encounter_boss_killed'    => ['name' => 'encounter_boss_killed',   'type' => 'bool',      'internal' => 'killed'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'boss' => [
            'mapper'   => InstanceBossMapper::class,
            'external' => 'encounter_boss_boss',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'attempts' => [
            'mapper'   => EncounterAttemptMapper::class,
            'table'    => 'encounter_attempt',
            'self'     => 'encounter_attempt_boss',
            'external' => null,
        ],
        'stats' => [
            'mapper'   => EncounterBossStatMapper::class,
            'table'    => 'encounter_boss_stat',
            'self'     => 'encounter_boss_stat_boss',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = EncounterBoss::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'encounter_boss';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'encounter_boss_id';
}
