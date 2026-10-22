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
        'achievement_type_id'       => ['name' => 'achievement_type_id',      'type' => 'int',       'internal' => 'id'],
        'achievement_type_name'     => ['name' => 'achievement_type_name',    'type' => 'string',    'internal' => 'name'],
        'achievement_type_desc'     => ['name' => 'achievement_type_desc',    'type' => 'string',    'internal' => 'description'],
        'achievement_type_category' => ['name' => 'achievement_type_category', 'type' => 'int',       'internal' => 'category'],
        'achievement_type_score1'   => ['name' => 'achievement_type_score1',  'type' => 'int',       'internal' => 'score1'],
        'achievement_type_score2'   => ['name' => 'achievement_type_score2',  'type' => 'int',       'internal' => 'score2'],
        'achievement_type_score3'   => ['name' => 'achievement_type_score3',  'type' => 'int',       'internal' => 'score3'],
        'achievement_type_score4'   => ['name' => 'achievement_type_score4',  'type' => 'int',       'internal' => 'score4'],
        'achievement_type_score5'   => ['name' => 'achievement_type_score5',  'type' => 'int',       'internal' => 'score5'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'category' => [
            'mapper'   => AchievementCategoryMapper::class,
            'external' => 'achievement_type_category',
        ],
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
