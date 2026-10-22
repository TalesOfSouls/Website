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
 * Account mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Account
 * @extends DataMapperFactory<T>
 */
class AccountMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'account_id'        => ['name' => 'account_id',       'type' => 'int',       'internal' => 'id'],
        'account_name'      => ['name' => 'account_name',     'type' => 'string',    'internal' => 'name'],
        'account_email'     => ['name' => 'account_email',    'type' => 'string',    'internal' => 'email'],
        'account_password'  => ['name' => 'account_password', 'type' => 'string',    'internal' => 'password'],
        'account_created'   => ['name' => 'account_created',  'type' => 'DateTime',  'internal' => 'created'],
        'account_lastlogin' => ['name' => 'account_lastlogin', 'type' => 'DateTime',  'internal' => 'lastLogin'],
        'account_status'    => ['name' => 'account_status',   'type' => 'int',       'internal' => 'status'],
        'account_role'      => ['name' => 'account_role',     'type' => 'int',       'internal' => 'role'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'characters' => [
            'mapper'   => CharacterMapper::class,
            'table'    => 'character',
            'self'     => 'character_account',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Account::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'account';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'account_id';
}
