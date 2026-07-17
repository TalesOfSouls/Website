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
        'account_id'           => ['name' => 'account_id',           'type' => 'int',      'internal' => 'id'],
        'account_name'        => ['name' => 'account_name',        'type' => 'string',   'internal' => 'name'],
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
