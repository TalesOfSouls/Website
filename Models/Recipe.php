<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Models;

/**
 * Recipe class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Recipe
{
    public int $id = 0;

    public Item $for;

    public int $cost = 0;

    public int $time = 0;

    /**
     * Ingredients
     *
     * @var Ingredient[]
     */
    public array $ingredients = [];
}