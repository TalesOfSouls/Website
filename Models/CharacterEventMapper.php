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
 * CharacterEvent mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of CharacterEvent
 * @extends DataMapperFactory<T>
 */
class CharacterEventMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'character_event_id'        => ['name' => 'character_event_id',       'type' => 'int',       'internal' => 'id'],
        'character_event_character' => ['name' => 'character_event_character', 'type' => 'int',       'internal' => 'character'],
        'character_event_type'      => ['name' => 'character_event_type',     'type' => 'string',    'internal' => 'type'],
        'character_event_title'     => ['name' => 'character_event_title',    'type' => 'string',    'internal' => 'title'],
        'character_event_detail'    => ['name' => 'character_event_detail',   'type' => 'string',    'internal' => 'detail'],
        'character_event_rarity'    => ['name' => 'character_event_rarity',   'type' => 'int',       'internal' => 'rarity'],
        'character_event_encounter' => ['name' => 'character_event_encounter', 'type' => 'int',       'internal' => 'encounter'],
        'character_event_datetime'  => ['name' => 'character_event_datetime', 'type' => 'DateTime',  'internal' => 'datetime'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = CharacterEvent::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'character_event';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'character_event_id';
}
