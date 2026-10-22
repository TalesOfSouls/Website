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
 * ItemIndividual mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of ItemIndividual
 * @extends DataMapperFactory<T>
 */
class ItemIndividualMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'item_individual_id'             => ['name' => 'item_individual_id',            'type' => 'int',       'internal' => 'id'],
        'item_individual_equipment_type' => ['name' => 'item_individual_equipment_type', 'type' => 'int',       'internal' => 'equipmentType'],
        'item_individual_name'           => ['name' => 'item_individual_name',          'type' => 'string',    'internal' => 'name'],
        'item_individual_rarity'         => ['name' => 'item_individual_rarity',        'type' => 'int',       'internal' => 'rarity'],
        'item_individual_level'          => ['name' => 'item_individual_level',         'type' => 'int',       'internal' => 'level'],
        'item_individual_character'      => ['name' => 'item_individual_character',     'type' => 'int',       'internal' => 'character'],
        'item_individual_created'        => ['name' => 'item_individual_created',       'type' => 'DateTime',  'internal' => 'created'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'rarity' => [
            'mapper'   => ItemRarityMapper::class,
            'external' => 'item_individual_rarity',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = ItemIndividual::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'item_individual';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'item_individual_id';
}
