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
 * Encounter mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Encounter
 * @extends DataMapperFactory<T>
 */
class EncounterMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'encounter_id'         => ['name' => 'encounter_id',        'type' => 'int',       'internal' => 'id'],
        'encounter_type'       => ['name' => 'encounter_type',      'type' => 'string',    'internal' => 'type'],
        'encounter_instance'   => ['name' => 'encounter_instance',  'type' => 'int',       'internal' => 'instance'],
        'encounter_difficulty' => ['name' => 'encounter_difficulty', 'type' => 'string',    'internal' => 'difficulty'],
        'encounter_start'      => ['name' => 'encounter_start',     'type' => 'DateTime',  'internal' => 'start'],
        'encounter_duration'   => ['name' => 'encounter_duration',  'type' => 'int',       'internal' => 'duration'],
        'encounter_success'    => ['name' => 'encounter_success',   'type' => 'bool',      'internal' => 'success'],
        'encounter_winner'     => ['name' => 'encounter_winner',    'type' => 'int',       'internal' => 'winner'],
        'encounter_score1'     => ['name' => 'encounter_score1',    'type' => 'int',       'internal' => 'score1'],
        'encounter_score2'     => ['name' => 'encounter_score2',    'type' => 'int',       'internal' => 'score2'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'instance' => [
            'mapper'   => InstanceMapper::class,
            'external' => 'encounter_instance',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'members' => [
            'mapper'   => EncounterMemberMapper::class,
            'table'    => 'encounter_member',
            'self'     => 'encounter_member_encounter',
            'external' => null,
        ],
        'bosses' => [
            'mapper'   => EncounterBossMapper::class,
            'table'    => 'encounter_boss',
            'self'     => 'encounter_boss_encounter',
            'external' => null,
        ],
        'scores' => [
            'mapper'   => EncounterScoreMapper::class,
            'table'    => 'encounter_score',
            'self'     => 'encounter_score_encounter',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Encounter::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'encounter';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'encounter_id';
}
