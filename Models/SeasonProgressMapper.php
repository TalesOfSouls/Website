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
 * SeasonProgress mapper class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of SeasonProgress
 * @extends DataMapperFactory<T>
 */
class SeasonProgressMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'season_progress_id'             => ['name' => 'season_progress_id',            'type' => 'int',       'internal' => 'id'],
        'season_progress_season'         => ['name' => 'season_progress_season',        'type' => 'int',       'internal' => 'season'],
        'season_progress_account'        => ['name' => 'season_progress_account',       'type' => 'int',       'internal' => 'account'],
        'season_progress_zones'          => ['name' => 'season_progress_zones',         'type' => 'int',       'internal' => 'zones'],
        'season_progress_elites'         => ['name' => 'season_progress_elites',        'type' => 'int',       'internal' => 'elites'],
        'season_progress_events'         => ['name' => 'season_progress_events',        'type' => 'int',       'internal' => 'events'],
        'season_progress_ladder_rating'  => ['name' => 'season_progress_ladder_rating', 'type' => 'int',       'internal' => 'ladderRating'],
        'season_progress_ladder_rank'    => ['name' => 'season_progress_ladder_rank',   'type' => 'int',       'internal' => 'ladderRank'],
        'season_progress_ladder_players' => ['name' => 'season_progress_ladder_players', 'type' => 'int',       'internal' => 'ladderPlayers'],
        'season_progress_ladder_wins'    => ['name' => 'season_progress_ladder_wins',   'type' => 'int',       'internal' => 'ladderWins'],
        'season_progress_ladder_losses'  => ['name' => 'season_progress_ladder_losses', 'type' => 'int',       'internal' => 'ladderLosses'],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = SeasonProgress::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'season_progress';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'season_progress_id';
}
