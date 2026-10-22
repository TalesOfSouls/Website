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
 * EncounterMember mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of EncounterMember
 * @extends DataMapperFactory<T>
 */
class EncounterMemberMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'encounter_member_id'               => ['name' => 'encounter_member_id',              'type' => 'int',       'internal' => 'id'],
        'encounter_member_encounter'        => ['name' => 'encounter_member_encounter',       'type' => 'int',       'internal' => 'encounter'],
        'encounter_member_character'        => ['name' => 'encounter_member_character',       'type' => 'int',       'internal' => 'character'],
        'encounter_member_team'             => ['name' => 'encounter_member_team',            'type' => 'int',       'internal' => 'team'],
        'encounter_member_role'             => ['name' => 'encounter_member_role',            'type' => 'string',    'internal' => 'role'],
        'encounter_member_damage'           => ['name' => 'encounter_member_damage',          'type' => 'int',       'internal' => 'damage'],
        'encounter_member_damage_single'    => ['name' => 'encounter_member_damage_single',   'type' => 'int',       'internal' => 'damageSingle'],
        'encounter_member_damage_aoe'       => ['name' => 'encounter_member_damage_aoe',      'type' => 'int',       'internal' => 'damageAoe'],
        'encounter_member_healing'          => ['name' => 'encounter_member_healing',         'type' => 'int',       'internal' => 'healing'],
        'encounter_member_healing_single'   => ['name' => 'encounter_member_healing_single',  'type' => 'int',       'internal' => 'healingSingle'],
        'encounter_member_healing_aoe'      => ['name' => 'encounter_member_healing_aoe',     'type' => 'int',       'internal' => 'healingAoe'],
        'encounter_member_healing_received' => ['name' => 'encounter_member_healing_received', 'type' => 'int',       'internal' => 'healingReceived'],
        'encounter_member_damage_taken'     => ['name' => 'encounter_member_damage_taken',    'type' => 'int',       'internal' => 'damageTaken'],
        'encounter_member_deaths'           => ['name' => 'encounter_member_deaths',          'type' => 'int',       'internal' => 'deaths'],
        'encounter_member_kills'            => ['name' => 'encounter_member_kills',           'type' => 'int',       'internal' => 'kills'],
        'encounter_member_assists'          => ['name' => 'encounter_member_assists',         'type' => 'int',       'internal' => 'assists'],
        'encounter_member_objectives'       => ['name' => 'encounter_member_objectives',      'type' => 'int',       'internal' => 'objectives'],
        'encounter_member_casts'            => ['name' => 'encounter_member_casts',           'type' => 'int',       'internal' => 'casts'],
        'encounter_member_apm'              => ['name' => 'encounter_member_apm',             'type' => 'float',     'internal' => 'apm'],
        'encounter_member_cooldown_usage'   => ['name' => 'encounter_member_cooldown_usage',  'type' => 'int',       'internal' => 'cooldownUsage'],
        'encounter_member_active'           => ['name' => 'encounter_member_active',          'type' => 'int',       'internal' => 'active'],
        'encounter_member_rating_before'    => ['name' => 'encounter_member_rating_before',   'type' => 'int',       'internal' => 'ratingBefore'],
        'encounter_member_rating_change'    => ['name' => 'encounter_member_rating_change',   'type' => 'int',       'internal' => 'ratingChange'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'character' => [
            'mapper'   => CharacterMapper::class,
            'external' => 'encounter_member_character',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'skills' => [
            'mapper'   => EncounterMemberSkillMapper::class,
            'table'    => 'encounter_member_skill',
            'self'     => 'encounter_member_skill_member',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = EncounterMember::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'encounter_member';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'encounter_member_id';
}
