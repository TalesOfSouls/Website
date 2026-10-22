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
 * PvpMode mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of PvpMode
 * @extends DataMapperFactory<T>
 */
class PvpModeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'pvp_mode_id'     => ['name' => 'pvp_mode_id',    'type' => 'int',       'internal' => 'id'],
        'pvp_mode_name'   => ['name' => 'pvp_mode_name',  'type' => 'string',    'internal' => 'name'],
        'pvp_mode_size'   => ['name' => 'pvp_mode_size',  'type' => 'int',       'internal' => 'size'],
        'pvp_mode_target' => ['name' => 'pvp_mode_target', 'type' => 'int',       'internal' => 'target'],
        'pvp_mode_unit'   => ['name' => 'pvp_mode_unit',  'type' => 'string',    'internal' => 'unit'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = PvpMode::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'pvp_mode';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'pvp_mode_id';
}
