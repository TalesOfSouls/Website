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
 * AccountAchievementTier mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of AccountAchievementTier
 * @extends DataMapperFactory<T>
 */
class AccountAchievementTierMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'account_achievement_tier_id'       => ['name' => 'account_achievement_tier_id',      'type' => 'int',       'internal' => 'id'],
        'account_achievement_tier_account'  => ['name' => 'account_achievement_tier_account', 'type' => 'int',       'internal' => 'account'],
        'account_achievement_tier_type'     => ['name' => 'account_achievement_tier_type',    'type' => 'int',       'internal' => 'type'],
        'account_achievement_tier_tier'     => ['name' => 'account_achievement_tier_tier',    'type' => 'int',       'internal' => 'tier'],
        'account_achievement_tier_datetime' => ['name' => 'account_achievement_tier_datetime', 'type' => 'DateTime',  'internal' => 'datetime'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = AccountAchievementTier::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'account_achievement_tier';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'account_achievement_tier_id';
}
