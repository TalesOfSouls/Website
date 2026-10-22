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
 * EncounterAttempt mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of EncounterAttempt
 * @extends DataMapperFactory<T>
 */
class EncounterAttemptMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'encounter_attempt_id'       => ['name' => 'encounter_attempt_id',      'type' => 'int',       'internal' => 'id'],
        'encounter_attempt_boss'     => ['name' => 'encounter_attempt_boss',    'type' => 'int',       'internal' => 'boss'],
        'encounter_attempt_no'       => ['name' => 'encounter_attempt_no',      'type' => 'int',       'internal' => 'no'],
        'encounter_attempt_duration' => ['name' => 'encounter_attempt_duration', 'type' => 'int',       'internal' => 'duration'],
        'encounter_attempt_hp_left'  => ['name' => 'encounter_attempt_hp_left', 'type' => 'int',       'internal' => 'hpLeft'],
        'encounter_attempt_kill'     => ['name' => 'encounter_attempt_kill',    'type' => 'bool',      'internal' => 'kill'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = EncounterAttempt::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'encounter_attempt';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'encounter_attempt_id';
}
