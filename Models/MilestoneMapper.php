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
 * Milestone mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Milestone
 * @extends DataMapperFactory<T>
 */
class MilestoneMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'milestone_id'          => ['name' => 'milestone_id',         'type' => 'int',       'internal' => 'id'],
        'milestone_title'       => ['name' => 'milestone_title',      'type' => 'string',    'internal' => 'title'],
        'milestone_description' => ['name' => 'milestone_description', 'type' => 'string',    'internal' => 'description'],
        'milestone_state'       => ['name' => 'milestone_state',      'type' => 'string',    'internal' => 'state'],
        'milestone_when'        => ['name' => 'milestone_when',       'type' => 'string',    'internal' => 'when'],
        'milestone_order'       => ['name' => 'milestone_order',      'type' => 'int',       'internal' => 'order'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Milestone::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'milestone';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'milestone_id';
}
