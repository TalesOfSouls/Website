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
        'character_id'           => ['name' => 'character_id',           'type' => 'int',      'internal' => 'id'],
        'character_name'        => ['name' => 'character_name',        'type' => 'string',   'internal' => 'name'],
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
