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
 * FeedbackComment mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of FeedbackComment
 * @extends DataMapperFactory<T>
 */
class FeedbackCommentMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'feedback_comment_id'       => ['name' => 'feedback_comment_id',      'type' => 'int',       'internal' => 'id'],
        'feedback_comment_feedback' => ['name' => 'feedback_comment_feedback', 'type' => 'int',       'internal' => 'feedback'],
        'feedback_comment_account'  => ['name' => 'feedback_comment_account', 'type' => 'int',       'internal' => 'account'],
        'feedback_comment_text'     => ['name' => 'feedback_comment_text',    'type' => 'string',    'internal' => 'text'],
        'feedback_comment_created'  => ['name' => 'feedback_comment_created', 'type' => 'DateTime',  'internal' => 'created'],
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
            'external' => 'feedback_comment_account',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = FeedbackComment::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'feedback_comment';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'feedback_comment_id';
}
