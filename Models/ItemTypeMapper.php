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
 * ItemType mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of ItemType
 * @extends DataMapperFactory<T>
 */
class ItemTypeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'item_type_id'           => ['name' => 'item_type_id',           'type' => 'int',      'internal' => 'id'],
        'item_type_name'        => ['name' => 'item_type_name',        'type' => 'string',   'internal' => 'name'],
        'item_type_tradable'           => ['name' => 'item_type_tradable',           'type' => 'bool',      'internal' => 'tradable'],
        //'item_type_description'        => ['name' => 'item_type_description',        'type' => 'string',   'internal' => 'description'],
        //'item_type_release_date'        => ['name' => 'item_type_release_date',        'type' => 'DateTime',   'internal' => 'releaseDate'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = ItemType::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'item_type';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'item_type_id';
}
