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
 * GuildMember mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of GuildMember
 * @extends DataMapperFactory<T>
 */
class GuildMemberMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'guild_member_id'        => ['name' => 'guild_member_id',       'type' => 'int',       'internal' => 'id'],
        'guild_member_character' => ['name' => 'guild_member_character', 'type' => 'int',       'internal' => 'character'],
        'guild_member_role'      => ['name' => 'guild_member_role',     'type' => 'int',       'internal' => 'role'],
        'guild_member_rank'      => ['name' => 'guild_member_rank',     'type' => 'int',       'internal' => 'rank'],
        'guild_member_guild'     => ['name' => 'guild_member_guild',    'type' => 'int',       'internal' => 'guild'],
        'guild_member_joined'    => ['name' => 'guild_member_joined',   'type' => 'DateTime',  'internal' => 'joined'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'character' => [
            'mapper'   => CharacterMapper::class,
            'external' => 'guild_member_character',
        ],
        'role' => [
            'mapper'   => GuildRoleMapper::class,
            'external' => 'guild_member_role',
        ],
        'rank' => [
            'mapper'   => GuildRankMapper::class,
            'external' => 'guild_member_rank',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = GuildMember::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'guild_member';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'guild_member_id';
}
