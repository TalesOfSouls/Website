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
 * Character mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Character
 * @extends DataMapperFactory<T>
 */
class CharacterMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'character_id'         => ['name' => 'character_id',        'type' => 'int',       'internal' => 'id'],
        'character_account'    => ['name' => 'character_account',   'type' => 'int',       'internal' => 'account'],
        'character_name'       => ['name' => 'character_name',      'type' => 'string',    'internal' => 'name'],
        'character_title'      => ['name' => 'character_title',     'type' => 'int',       'internal' => 'title'],
        'character_server'     => ['name' => 'character_server',    'type' => 'int',       'internal' => 'server'],
        'character_class'      => ['name' => 'character_class',     'type' => 'int',       'internal' => 'class'],
        'character_race'       => ['name' => 'character_race',      'type' => 'int',       'internal' => 'race'],
        'character_xp'         => ['name' => 'character_xp',        'type' => 'int',       'internal' => 'xp'],
        'character_guild'      => ['name' => 'character_guild',     'type' => 'int',       'internal' => 'guild'],
        'character_playtime'   => ['name' => 'character_playtime',  'type' => 'int',       'internal' => 'playtime'],
        'character_created'    => ['name' => 'character_created',   'type' => 'DateTime',  'internal' => 'created'],
        'character_season'     => ['name' => 'character_season',    'type' => 'int',       'internal' => 'season'],
        'character_hardcore'   => ['name' => 'character_hardcore',  'type' => 'bool',      'internal' => 'hardcore'],
        'character_dead'       => ['name' => 'character_dead',      'type' => 'bool',      'internal' => 'dead'],
        'character_item_level' => ['name' => 'character_item_level', 'type' => 'int',       'internal' => 'itemLevel'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'account' => [
            'mapper'   => AccountMapper::class,
            'external' => 'character_account',
        ],
        'server' => [
            'mapper'   => ServerMapper::class,
            'external' => 'character_server',
        ],
        'class' => [
            'mapper'   => PlayerClassMapper::class,
            'external' => 'character_class',
        ],
        'race' => [
            'mapper'   => RaceMapper::class,
            'external' => 'character_race',
        ],
        'guild' => [
            'mapper'   => GuildMapper::class,
            'external' => 'character_guild',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Character::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'character';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'character_id';
}
