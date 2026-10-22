<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Models;

/**
 * Seasonal progress of an account.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class SeasonProgress
{
    public int $id = 0;

    public int $season = 0;

    public int $account = 0;

    public int $zones = 0;

    public int $elites = 0;

    public int $events = 0;

    public ?int $ladderRating = null;

    public ?int $ladderRank = null;

    public ?int $ladderPlayers = null;

    public ?int $ladderWins = null;

    public ?int $ladderLosses = null;

    /**
     * Constructor.
     *
     * @param int $id Model id
     *
     * @since 1.0.0
     */
    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }
}
