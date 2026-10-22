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
 * EncounterScore mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of EncounterScore
 * @extends DataMapperFactory<T>
 */
class EncounterScoreMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'encounter_score_id'        => ['name' => 'encounter_score_id',       'type' => 'int',       'internal' => 'id'],
        'encounter_score_encounter' => ['name' => 'encounter_score_encounter', 'type' => 'int',       'internal' => 'encounter'],
        'encounter_score_time'      => ['name' => 'encounter_score_time',     'type' => 'int',       'internal' => 'time'],
        'encounter_score_score1'    => ['name' => 'encounter_score_score1',   'type' => 'int',       'internal' => 'score1'],
        'encounter_score_score2'    => ['name' => 'encounter_score_score2',   'type' => 'int',       'internal' => 'score2'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = EncounterScore::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'encounter_score';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'encounter_score_id';
}
