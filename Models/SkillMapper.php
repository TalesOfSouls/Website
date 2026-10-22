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
 * Skill mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Skill
 * @extends DataMapperFactory<T>
 */
class SkillMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'skill_id'       => ['name' => 'skill_id',      'type' => 'int',       'internal' => 'id'],
        'skill_name'     => ['name' => 'skill_name',    'type' => 'string',    'internal' => 'name'],
        'skill_note'     => ['name' => 'skill_note',    'type' => 'string',    'internal' => 'note'],
        'skill_cooldown' => ['name' => 'skill_cooldown', 'type' => 'int',       'internal' => 'cooldown'],
        'skill_kind'     => ['name' => 'skill_kind',    'type' => 'string',    'internal' => 'kind'],
        'skill_target'   => ['name' => 'skill_target',  'type' => 'string',    'internal' => 'target'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Skill::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'skill';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'skill_id';
}
