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
 * CharacterProficiency mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of CharacterProficiency
 * @extends DataMapperFactory<T>
 */
class CharacterProficiencyMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'character_proficiency_id'          => ['name' => 'character_proficiency_id',         'type' => 'int',       'internal' => 'id'],
        'character_proficiency_character'   => ['name' => 'character_proficiency_character',  'type' => 'int',       'internal' => 'character'],
        'character_proficiency_brewing'     => ['name' => 'character_proficiency_brewing',    'type' => 'int',       'internal' => 'brewing'],
        'character_proficiency_forging'     => ['name' => 'character_proficiency_forging',    'type' => 'int',       'internal' => 'forging'],
        'character_proficiency_enchanting'  => ['name' => 'character_proficiency_enchanting', 'type' => 'int',       'internal' => 'enchanting'],
        'character_proficiency_cooking'     => ['name' => 'character_proficiency_cooking',    'type' => 'int',       'internal' => 'cooking'],
        'character_proficiency_tailoring'   => ['name' => 'character_proficiency_tailoring',  'type' => 'int',       'internal' => 'tailoring'],
        'character_proficiency_breeding'    => ['name' => 'character_proficiency_breeding',   'type' => 'int',       'internal' => 'breeding'],
        'character_proficiency_taming'      => ['name' => 'character_proficiency_taming',     'type' => 'int',       'internal' => 'taming'],
        'character_proficiency_mining'      => ['name' => 'character_proficiency_mining',     'type' => 'int',       'internal' => 'mining'],
        'character_proficiency_woodcutting' => ['name' => 'character_proficiency_woodcutting', 'type' => 'int',       'internal' => 'woodcutting'],
        'character_proficiency_gathering'   => ['name' => 'character_proficiency_gathering',  'type' => 'int',       'internal' => 'gathering'],
        'character_proficiency_stealing'    => ['name' => 'character_proficiency_stealing',   'type' => 'int',       'internal' => 'stealing'],
        'character_proficiency_stealth'     => ['name' => 'character_proficiency_stealth',    'type' => 'int',       'internal' => 'stealth'],
        'character_proficiency_archeology'  => ['name' => 'character_proficiency_archeology', 'type' => 'int',       'internal' => 'archeology'],
        'character_proficiency_fishing'     => ['name' => 'character_proficiency_fishing',    'type' => 'int',       'internal' => 'fishing'],
        'character_proficiency_fletching'   => ['name' => 'character_proficiency_fletching',  'type' => 'int',       'internal' => 'fletching'],
        'character_proficiency_diving'      => ['name' => 'character_proficiency_diving',     'type' => 'int',       'internal' => 'diving'],
        'character_proficiency_farming'     => ['name' => 'character_proficiency_farming',    'type' => 'int',       'internal' => 'farming'],
        'character_proficiency_endurance'   => ['name' => 'character_proficiency_endurance',  'type' => 'int',       'internal' => 'endurance'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = CharacterProficiency::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'character_proficiency';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'character_proficiency_id';
}
