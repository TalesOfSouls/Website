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
 * FeedbackCommentVote mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of FeedbackCommentVote
 * @extends DataMapperFactory<T>
 */
class FeedbackCommentVoteMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'feedback_comment_vote_id'       => ['name' => 'feedback_comment_vote_id',      'type' => 'int',       'internal' => 'id'],
        'feedback_comment_vote_comment'  => ['name' => 'feedback_comment_vote_comment', 'type' => 'int',       'internal' => 'comment'],
        'feedback_comment_vote_account'  => ['name' => 'feedback_comment_vote_account', 'type' => 'int',       'internal' => 'account'],
        'feedback_comment_vote_value'    => ['name' => 'feedback_comment_vote_value',   'type' => 'int',       'internal' => 'value'],
        'feedback_comment_vote_datetime' => ['name' => 'feedback_comment_vote_datetime', 'type' => 'DateTime',  'internal' => 'datetime'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = FeedbackCommentVote::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'feedback_comment_vote';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'feedback_comment_vote_id';
}
