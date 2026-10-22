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
 * SeasonBoss mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of SeasonBoss
 * @extends DataMapperFactory<T>
 */
class SeasonBossMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'season_boss_id'     => ['name' => 'season_boss_id',    'type' => 'int',       'internal' => 'id'],
        'season_boss_season' => ['name' => 'season_boss_season', 'type' => 'int',       'internal' => 'season'],
        'season_boss_name'   => ['name' => 'season_boss_name',  'type' => 'string',    'internal' => 'name'],
        'season_boss_order'  => ['name' => 'season_boss_order', 'type' => 'int',       'internal' => 'order'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = SeasonBoss::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'season_boss';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'season_boss_id';
}
