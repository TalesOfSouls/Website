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
 * Recipe mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Recipe
 * @extends DataMapperFactory<T>
 */
class RecipeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'item_recipe_id'         => ['name' => 'item_recipe_id',           'type' => 'int',      'internal' => 'id'],
        'item_recipe_new'        => ['name' => 'item_recipe_new',        'type' => 'int',   'internal' => 'for'],
        'item_recipe_cost'        => ['name' => 'item_recipe_cost',        'type' => 'int',   'internal' => 'cost'],
        'item_recipe_time'        => ['name' => 'item_recipe_time',        'type' => 'int',   'internal' => 'time'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'for' => [
            'mapper'   => ItemMapper::class,
            'external' => 'item_recipe_new',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'ingredients' => [
            'mapper'   => IngredientMapper::class,
            'table'    => 'item_recipe_item',
            'self'     => 'item_recipe_item_recipe',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Recipe::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'item_recipe';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'item_recipe_id';
}
