<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Model
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Models;

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * EquipmentSlot mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of EquipmentSlot
 * @extends DataMapperFactory<T>
 */
class EquipmentSlotMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'equipment_slot_id'           => ['name' => 'equipment_slot_id',           'type' => 'int',      'internal' => 'id'],
        'equipment_slot_name'        => ['name' => 'equipment_slot_name',        'type' => 'string',   'internal' => 'name'],
        //'equipment_slot_description'        => ['name' => 'equipment_slot_description',        'type' => 'string',   'internal' => 'description'],
        //'equipment_slot_release_date'        => ['name' => 'equipment_slot_release_date',        'type' => 'DateTime',   'internal' => 'releaseDate'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = EquipmentSlot::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'equipment_slot';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'equipment_slot_id';
}
