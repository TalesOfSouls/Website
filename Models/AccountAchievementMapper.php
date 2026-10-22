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
 * AccountAchievement mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of AccountAchievement
 * @extends DataMapperFactory<T>
 */
class AccountAchievementMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'account_achievement_id'       => ['name' => 'account_achievement_id',      'type' => 'int',       'internal' => 'id'],
        'account_achievement_account'  => ['name' => 'account_achievement_account', 'type' => 'int',       'internal' => 'account'],
        'account_achievement_type'     => ['name' => 'account_achievement_type',    'type' => 'int',       'internal' => 'type'],
        'account_achievement_score'    => ['name' => 'account_achievement_score',   'type' => 'int',       'internal' => 'score'],
        'account_achievement_datetime' => ['name' => 'account_achievement_datetime', 'type' => 'DateTime',  'internal' => 'datetime'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'type' => [
            'mapper'   => AchievementTypeMapper::class,
            'external' => 'account_achievement_type',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = AccountAchievement::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'account_achievement';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'account_achievement_id';
}
