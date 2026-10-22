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
 * DevelopmentArea mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of DevelopmentArea
 * @extends DataMapperFactory<T>
 */
class DevelopmentAreaMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'development_area_id'       => ['name' => 'development_area_id',      'type' => 'int',       'internal' => 'id'],
        'development_area_name'     => ['name' => 'development_area_name',    'type' => 'string',    'internal' => 'name'],
        'development_area_progress' => ['name' => 'development_area_progress', 'type' => 'int',       'internal' => 'progress'],
        'development_area_order'    => ['name' => 'development_area_order',   'type' => 'int',       'internal' => 'order'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = DevelopmentArea::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'development_area';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'development_area_id';
}
