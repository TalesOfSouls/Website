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
 * PlayerClass mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of PlayerClass
 * @extends DataMapperFactory<T>
 */
class PlayerClassMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'class_id'           => ['name' => 'class_id',           'type' => 'int',      'internal' => 'id'],
        'class_name'        => ['name' => 'class_name',        'type' => 'string',   'internal' => 'name'],
        'class_description'        => ['name' => 'class_description',        'type' => 'string',   'internal' => 'description'],
        'class_parent'        => ['name' => 'class_parent',        'type' => 'int',   'internal' => 'parent'],
        'class_release_date'        => ['name' => 'class_release_date',        'type' => 'DateTime',   'internal' => 'releaseDate'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = PlayerClass::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'class';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'class_id';
}
