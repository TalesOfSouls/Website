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
 * FeedbackStateLog mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of FeedbackStateLog
 * @extends DataMapperFactory<T>
 */
class FeedbackStateLogMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'feedback_state_log_id'       => ['name' => 'feedback_state_log_id',      'type' => 'int',       'internal' => 'id'],
        'feedback_state_log_feedback' => ['name' => 'feedback_state_log_feedback', 'type' => 'int',       'internal' => 'feedback'],
        'feedback_state_log_state'    => ['name' => 'feedback_state_log_state',   'type' => 'string',    'internal' => 'state'],
        'feedback_state_log_datetime' => ['name' => 'feedback_state_log_datetime', 'type' => 'DateTime',  'internal' => 'datetime'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = FeedbackStateLog::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'feedback_state_log';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'feedback_state_log_id';
}
