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
 * Ingredient mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Ingredient
 * @extends DataMapperFactory<T>
 */
class IngredientMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'item_recipe_item_id'           => ['name' => 'item_recipe_item_id',           'type' => 'int',      'internal' => 'id'],
        'item_recipe_item_item'        => ['name' => 'item_recipe_item_item',        'type' => 'int',   'internal' => 'item'],
        'item_recipe_item_recipe'        => ['name' => 'item_recipe_item_recipe',        'type' => 'int',   'internal' => 'recipe'],
    ];

    public const OWNS_ONE = [
        'item' => [
            'mapper'   => ItemMapper::class,
            'external' => 'item_recipe_item_item',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Ingredient::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'item_recipe_item';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'item_recipe_item_id';
}
