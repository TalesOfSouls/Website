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
 * SeasonBossKill mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of SeasonBossKill
 * @extends DataMapperFactory<T>
 */
class SeasonBossKillMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'season_boss_kill_id'       => ['name' => 'season_boss_kill_id',      'type' => 'int',       'internal' => 'id'],
        'season_boss_kill_boss'     => ['name' => 'season_boss_kill_boss',    'type' => 'int',       'internal' => 'boss'],
        'season_boss_kill_account'  => ['name' => 'season_boss_kill_account', 'type' => 'int',       'internal' => 'account'],
        'season_boss_kill_attempts' => ['name' => 'season_boss_kill_attempts', 'type' => 'int',       'internal' => 'attempts'],
        'season_boss_kill_killed'   => ['name' => 'season_boss_kill_killed',  'type' => 'bool',      'internal' => 'killed'],
        'season_boss_kill_datetime' => ['name' => 'season_boss_kill_datetime', 'type' => 'DateTime',  'internal' => 'datetime'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = SeasonBossKill::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'season_boss_kill';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'season_boss_kill_id';
}
