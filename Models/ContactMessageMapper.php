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
 * ContactMessage mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of ContactMessage
 * @extends DataMapperFactory<T>
 */
class ContactMessageMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'contact_message_id'      => ['name' => 'contact_message_id',     'type' => 'int',       'internal' => 'id'],
        'contact_message_account' => ['name' => 'contact_message_account', 'type' => 'int',       'internal' => 'account'],
        'contact_message_name'    => ['name' => 'contact_message_name',   'type' => 'string',    'internal' => 'name'],
        'contact_message_email'   => ['name' => 'contact_message_email',  'type' => 'string',    'internal' => 'email'],
        'contact_message_subject' => ['name' => 'contact_message_subject', 'type' => 'string',    'internal' => 'subject'],
        'contact_message_text'    => ['name' => 'contact_message_text',   'type' => 'string',    'internal' => 'text'],
        'contact_message_created' => ['name' => 'contact_message_created', 'type' => 'DateTime',  'internal' => 'created'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = ContactMessage::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'contact_message';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'contact_message_id';
}
