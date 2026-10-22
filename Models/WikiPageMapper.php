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
 * WikiPage mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of WikiPage
 * @extends DataMapperFactory<T>
 */
class WikiPageMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'wiki_page_id'        => ['name' => 'wiki_page_id',       'type' => 'int',       'internal' => 'id'],
        'wiki_page_slug'      => ['name' => 'wiki_page_slug',     'type' => 'string',    'internal' => 'slug'],
        'wiki_page_title'     => ['name' => 'wiki_page_title',    'type' => 'string',    'internal' => 'title'],
        'wiki_page_nav_title' => ['name' => 'wiki_page_nav_title', 'type' => 'string',    'internal' => 'navTitle'],
        'wiki_page_content'   => ['name' => 'wiki_page_content',  'type' => 'string',    'internal' => 'content'],
        'wiki_page_module'    => ['name' => 'wiki_page_module',   'type' => 'string',    'internal' => 'module'],
        'wiki_page_nav_order' => ['name' => 'wiki_page_nav_order', 'type' => 'int',       'internal' => 'navOrder'],
        'wiki_page_account'   => ['name' => 'wiki_page_account',  'type' => 'int',       'internal' => 'account'],
        'wiki_page_updated'   => ['name' => 'wiki_page_updated',  'type' => 'DateTime',  'internal' => 'updated'],
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
            'external' => 'wiki_page_account',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = WikiPage::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'wiki_page';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'wiki_page_id';
}
