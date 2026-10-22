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
 * EncounterMemberSkill mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of EncounterMemberSkill
 * @extends DataMapperFactory<T>
 */
class EncounterMemberSkillMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'encounter_member_skill_id'       => ['name' => 'encounter_member_skill_id',      'type' => 'int',       'internal' => 'id'],
        'encounter_member_skill_member'   => ['name' => 'encounter_member_skill_member',  'type' => 'int',       'internal' => 'member'],
        'encounter_member_skill_skill'    => ['name' => 'encounter_member_skill_skill',   'type' => 'int',       'internal' => 'skill'],
        'encounter_member_skill_casts'    => ['name' => 'encounter_member_skill_casts',   'type' => 'int',       'internal' => 'casts'],
        'encounter_member_skill_possible' => ['name' => 'encounter_member_skill_possible', 'type' => 'int',       'internal' => 'possible'],
        'encounter_member_skill_amount'   => ['name' => 'encounter_member_skill_amount',  'type' => 'int',       'internal' => 'amount'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'skill' => [
            'mapper'   => SkillMapper::class,
            'external' => 'encounter_member_skill_skill',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = EncounterMemberSkill::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'encounter_member_skill';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'encounter_member_skill_id';
}
