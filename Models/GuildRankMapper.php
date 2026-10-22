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
 * GuildRank mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of GuildRank
 * @extends DataMapperFactory<T>
 */
class GuildRankMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'guild_rank_id'    => ['name' => 'guild_rank_id',   'type' => 'int',       'internal' => 'id'],
        'guild_rank_name'  => ['name' => 'guild_rank_name', 'type' => 'string',    'internal' => 'name'],
        'guild_rank_color' => ['name' => 'guild_rank_color', 'type' => 'int',       'internal' => 'color'],
        'guild_rank_guild' => ['name' => 'guild_rank_guild', 'type' => 'int',       'internal' => 'guild'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = GuildRank::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'guild_rank';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'guild_rank_id';
}
