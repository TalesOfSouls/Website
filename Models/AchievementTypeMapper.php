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
 * AchievementType mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of AchievementType
 * @extends DataMapperFactory<T>
 */
class AchievementTypeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'achievement_type_id'           => ['name' => 'achievement_type_id',           'type' => 'int',      'internal' => 'id'],
        'achievement_type_name'        => ['name' => 'achievement_type_name',        'type' => 'string',   'internal' => 'name'],
        //'achievement_type_description'        => ['name' => 'achievement_type_description',        'type' => 'string',   'internal' => 'description'],
        //'achievement_type_release_date'        => ['name' => 'achievement_type_release_date',        'type' => 'DateTime',   'internal' => 'releaseDate'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = AchievementType::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'achievement_type';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'achievement_type_id';
}
