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
 * EquipmentType mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of EquipmentType
 * @extends DataMapperFactory<T>
 */
class EquipmentTypeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'equipment_type_id'           => ['name' => 'equipment_type_id',           'type' => 'int',      'internal' => 'id'],
        'equipment_type_name'        => ['name' => 'equipment_type_name',        'type' => 'string',   'internal' => 'name'],
        'equipment_type_slot'           => ['name' => 'equipment_type_slot',           'type' => 'int',      'internal' => 'slot'],
        //'equipment_type_description'        => ['name' => 'equipment_type_description',        'type' => 'string',   'internal' => 'description'],
        //'equipment_type_release_date'        => ['name' => 'equipment_type_release_date',        'type' => 'DateTime',   'internal' => 'releaseDate'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = EquipmentType::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'equipment_type';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'equipment_type_id';
}
