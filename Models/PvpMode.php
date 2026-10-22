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
 * PvP mode.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class PvpMode
{
    public int $id = 0;

    public string $name = '';

    /**
     * Players per team
     *
     * @var int
     * @since 1.0.0
     */
    public int $size = 0;

    /**
     * Score needed to win
     *
     * @var int
     * @since 1.0.0
     */
    public int $target = 0;

    /**
     * Unit of the score (rounds, kills, ...)
     *
     * @var string
     * @since 1.0.0
     */
    public string $unit = '';

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
