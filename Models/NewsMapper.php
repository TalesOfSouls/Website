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
 * News mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of News
 * @extends DataMapperFactory<T>
 */
class NewsMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'news_id'        => ['name' => 'news_id',       'type' => 'int',       'internal' => 'id'],
        'news_slug'      => ['name' => 'news_slug',     'type' => 'string',    'internal' => 'slug'],
        'news_title'     => ['name' => 'news_title',    'type' => 'string',    'internal' => 'title'],
        'news_summary'   => ['name' => 'news_summary',  'type' => 'string',    'internal' => 'summary'],
        'news_image'     => ['name' => 'news_image',    'type' => 'string',    'internal' => 'image'],
        'news_content'   => ['name' => 'news_content',  'type' => 'string',    'internal' => 'content'],
        'news_account'   => ['name' => 'news_account',  'type' => 'int',       'internal' => 'account'],
        'news_status'    => ['name' => 'news_status',   'type' => 'string',    'internal' => 'status'],
        'news_published' => ['name' => 'news_published', 'type' => 'DateTime',  'internal' => 'published'],
        'news_created'   => ['name' => 'news_created',  'type' => 'DateTime',  'internal' => 'created'],
        'news_updated'   => ['name' => 'news_updated',  'type' => 'DateTime',  'internal' => 'updated'],
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
            'external' => 'news_account',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = News::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'news';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'news_id';
}
