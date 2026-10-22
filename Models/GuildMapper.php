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
 * Guild mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Guild
 * @extends DataMapperFactory<T>
 */
class GuildMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'guild_id'          => ['name' => 'guild_id',         'type' => 'int',       'internal' => 'id'],
        'guild_server'      => ['name' => 'guild_server',     'type' => 'int',       'internal' => 'server'],
        'guild_name'        => ['name' => 'guild_name',       'type' => 'string',    'internal' => 'name'],
        'guild_tag'         => ['name' => 'guild_tag',        'type' => 'string',    'internal' => 'tag'],
        'guild_status'      => ['name' => 'guild_status',     'type' => 'int',       'internal' => 'status'],
        'guild_wvw'         => ['name' => 'guild_wvw',        'type' => 'bool',      'internal' => 'wvw'],
        'guild_pvp'         => ['name' => 'guild_pvp',        'type' => 'bool',      'internal' => 'pvp'],
        'guild_raid'        => ['name' => 'guild_raid',       'type' => 'bool',      'internal' => 'raid'],
        'guild_dungeon'     => ['name' => 'guild_dungeon',    'type' => 'bool',      'internal' => 'dungeon'],
        'guild_pve'         => ['name' => 'guild_pve',        'type' => 'bool',      'internal' => 'pve'],
        'guild_seriousness' => ['name' => 'guild_seriousness', 'type' => 'int',       'internal' => 'seriousness'],
        'guild_description' => ['name' => 'guild_description', 'type' => 'string',    'internal' => 'description'],
        'guild_created'     => ['name' => 'guild_created',    'type' => 'DateTime',  'internal' => 'created'],
        'guild_recruiting'  => ['name' => 'guild_recruiting', 'type' => 'bool',      'internal' => 'recruiting'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'server' => [
            'mapper'   => ServerMapper::class,
            'external' => 'guild_server',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'members' => [
            'mapper'   => GuildMemberMapper::class,
            'table'    => 'guild_member',
            'self'     => 'guild_member_guild',
            'external' => null,
        ],
        'ranks' => [
            'mapper'   => GuildRankMapper::class,
            'table'    => 'guild_rank',
            'self'     => 'guild_rank_guild',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Guild::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'guild';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'guild_id';
}
