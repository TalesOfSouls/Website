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
 * Score of a PvP match at a point in time.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class EncounterScore
{
    public int $id = 0;

    public int $encounter = 0;

    /**
     * Seconds since the match start
     *
     * @var int
     * @since 1.0.0
     */
    public int $time = 0;

    public int $score1 = 0;

    public int $score2 = 0;

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
