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
 * FeedbackVote mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of FeedbackVote
 * @extends DataMapperFactory<T>
 */
class FeedbackVoteMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'feedback_vote_id'       => ['name' => 'feedback_vote_id',      'type' => 'int',       'internal' => 'id'],
        'feedback_vote_feedback' => ['name' => 'feedback_vote_feedback', 'type' => 'int',       'internal' => 'feedback'],
        'feedback_vote_account'  => ['name' => 'feedback_vote_account', 'type' => 'int',       'internal' => 'account'],
        'feedback_vote_value'    => ['name' => 'feedback_vote_value',   'type' => 'int',       'internal' => 'value'],
        'feedback_vote_datetime' => ['name' => 'feedback_vote_datetime', 'type' => 'DateTime',  'internal' => 'datetime'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = FeedbackVote::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'feedback_vote';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'feedback_vote_id';
}
