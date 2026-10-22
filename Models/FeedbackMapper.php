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
 * Feedback mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Feedback
 * @extends DataMapperFactory<T>
 */
class FeedbackMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'feedback_id'                => ['name' => 'feedback_id',               'type' => 'int',       'internal' => 'id'],
        'feedback_account'           => ['name' => 'feedback_account',          'type' => 'int',       'internal' => 'account'],
        'feedback_type'              => ['name' => 'feedback_type',             'type' => 'string',    'internal' => 'type'],
        'feedback_state'             => ['name' => 'feedback_state',            'type' => 'string',    'internal' => 'state'],
        'feedback_title'             => ['name' => 'feedback_title',            'type' => 'string',    'internal' => 'title'],
        'feedback_text'              => ['name' => 'feedback_text',             'type' => 'string',    'internal' => 'text'],
        'feedback_created'           => ['name' => 'feedback_created',          'type' => 'DateTime',  'internal' => 'created'],
        'feedback_updated'           => ['name' => 'feedback_updated',          'type' => 'DateTime',  'internal' => 'updated'],
        'feedback_response'          => ['name' => 'feedback_response',         'type' => 'string',    'internal' => 'response'],
        'feedback_response_account'  => ['name' => 'feedback_response_account', 'type' => 'int',       'internal' => 'responseAccount'],
        'feedback_response_datetime' => ['name' => 'feedback_response_datetime', 'type' => 'DateTime',  'internal' => 'responseDatetime'],
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
            'external' => 'feedback_account',
        ],
        'responseAccount' => [
            'mapper'   => AccountMapper::class,
            'external' => 'feedback_response_account',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'comments' => [
            'mapper'   => FeedbackCommentMapper::class,
            'table'    => 'feedback_comment',
            'self'     => 'feedback_comment_feedback',
            'external' => null,
        ],
        'history' => [
            'mapper'   => FeedbackStateLogMapper::class,
            'table'    => 'feedback_state_log',
            'self'     => 'feedback_state_log_feedback',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Feedback::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'feedback';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'feedback_id';
}
