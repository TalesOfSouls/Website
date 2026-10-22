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
 * EncounterBossStat mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of EncounterBossStat
 * @extends DataMapperFactory<T>
 */
class EncounterBossStatMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'encounter_boss_stat_id'       => ['name' => 'encounter_boss_stat_id',      'type' => 'int',       'internal' => 'id'],
        'encounter_boss_stat_boss'     => ['name' => 'encounter_boss_stat_boss',    'type' => 'int',       'internal' => 'boss'],
        'encounter_boss_stat_member'   => ['name' => 'encounter_boss_stat_member',  'type' => 'int',       'internal' => 'member'],
        'encounter_boss_stat_damage'   => ['name' => 'encounter_boss_stat_damage',  'type' => 'int',       'internal' => 'damage'],
        'encounter_boss_stat_healing'  => ['name' => 'encounter_boss_stat_healing', 'type' => 'int',       'internal' => 'healing'],
        'encounter_boss_stat_received' => ['name' => 'encounter_boss_stat_received', 'type' => 'int',       'internal' => 'received'],
        'encounter_boss_stat_taken'    => ['name' => 'encounter_boss_stat_taken',   'type' => 'int',       'internal' => 'taken'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = EncounterBossStat::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'encounter_boss_stat';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'encounter_boss_stat_id';
}
