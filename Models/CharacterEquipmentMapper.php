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
 * CharacterEquipment mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of CharacterEquipment
 * @extends DataMapperFactory<T>
 */
class CharacterEquipmentMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'character_equipment_id'           => ['name' => 'character_equipment_id',          'type' => 'int',       'internal' => 'id'],
        'character_equipment_character'    => ['name' => 'character_equipment_character',   'type' => 'int',       'internal' => 'character'],
        'character_equipment_head'         => ['name' => 'character_equipment_head',        'type' => 'int',       'internal' => 'head'],
        'character_equipment_neck'         => ['name' => 'character_equipment_neck',        'type' => 'int',       'internal' => 'neck'],
        'character_equipment_body'         => ['name' => 'character_equipment_body',        'type' => 'int',       'internal' => 'body'],
        'character_equipment_belt'         => ['name' => 'character_equipment_belt',        'type' => 'int',       'internal' => 'belt'],
        'character_equipment_pants'        => ['name' => 'character_equipment_pants',       'type' => 'int',       'internal' => 'pants'],
        'character_equipment_boots'        => ['name' => 'character_equipment_boots',       'type' => 'int',       'internal' => 'boots'],
        'character_equipment_ring1'        => ['name' => 'character_equipment_ring1',       'type' => 'int',       'internal' => 'ring1'],
        'character_equipment_ring2'        => ['name' => 'character_equipment_ring2',       'type' => 'int',       'internal' => 'ring2'],
        'character_equipment_hand1'        => ['name' => 'character_equipment_hand1',       'type' => 'int',       'internal' => 'hand1'],
        'character_equipment_hand2'        => ['name' => 'character_equipment_hand2',       'type' => 'int',       'internal' => 'hand2'],
        'character_equipment_hand1_alt'    => ['name' => 'character_equipment_hand1_alt',   'type' => 'int',       'internal' => 'hand1Alt'],
        'character_equipment_hand2_alt'    => ['name' => 'character_equipment_hand2_alt',   'type' => 'int',       'internal' => 'hand2Alt'],
        'character_equipment_arms'         => ['name' => 'character_equipment_arms',        'type' => 'int',       'internal' => 'arms'],
        'character_equipment_belt_attach'  => ['name' => 'character_equipment_belt_attach', 'type' => 'int',       'internal' => 'beltAttach'],
        'character_equipment_belt_trinket' => ['name' => 'character_equipment_belt_trinket', 'type' => 'int',       'internal' => 'beltTrinket'],
        'character_equipment_shoulder'     => ['name' => 'character_equipment_shoulder',    'type' => 'int',       'internal' => 'shoulder'],
        'character_equipment_back'         => ['name' => 'character_equipment_back',        'type' => 'int',       'internal' => 'back'],
        'character_equipment_hands'        => ['name' => 'character_equipment_hands',       'type' => 'int',       'internal' => 'hands'],
        'character_equipment_artifact'     => ['name' => 'character_equipment_artifact',    'type' => 'int',       'internal' => 'artifact'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = CharacterEquipment::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'character_equipment';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'character_equipment_id';
}
