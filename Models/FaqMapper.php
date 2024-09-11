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
 * Faq mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Faq
 * @extends DataMapperFactory<T>
 */
class FaqMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'faq_id'           => ['name' => 'faq_id',           'type' => 'int',      'internal' => 'id'],
        'faq_order'        => ['name' => 'faq_order',        'type' => 'int',   'internal' => 'order'],
        'faq_milestone'        => ['name' => 'faq_milestone',        'type' => 'string',   'internal' => 'milestone'],
        'faq_category'        => ['name' => 'faq_category',        'type' => 'string',   'internal' => 'category'],
        'faq_question'        => ['name' => 'faq_question',        'type' => 'string',   'internal' => 'question'],
        'faq_answer'        => ['name' => 'faq_answer',        'type' => 'string',   'internal' => 'answer'],
        'faq_datetime'        => ['name' => 'faq_datetime',        'type' => 'DateTime',   'internal' => 'datetime'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Faq::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'faq';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'faq_id';
}
