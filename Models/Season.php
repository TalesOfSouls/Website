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
 * Season.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Season
{
    public int $id = 0;

    public string $name = '';

    public \DateTime $start;

    public \DateTime $end;

    /**
     * Name of the world dungeon
     *
     * @var string
     * @since 1.0.0
     */
    public string $dungeon = '';

    /**
     * Zones of the world dungeon
     *
     * @var int
     * @since 1.0.0
     */
    public int $zones = 0;

    /**
     * Elite bosses of the world dungeon
     *
     * @var int
     * @since 1.0.0
     */
    public int $elites = 0;

    /**
     * Season has a PvP ladder
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $ladder = false;

    /**
     * Seasonal open world event
     *
     * @var string
     * @since 1.0.0
     */
    public string $event = '';

    /**
     * Main bosses of the world dungeon
     *
     * @var SeasonBoss[]
     * @since 1.0.0
     */
    public array $bosses = [];

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
        $this->start = new \DateTime();
        $this->end = new \DateTime();
    }

    /**
     * Is the season running right now?
     *
     * @param null|\DateTimeInterface $now Current date
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isCurrent(?\DateTimeInterface $now = null) : bool
    {
        $now ??= new \DateTime();

        return $this->start <= $now && $this->end >= $now;
    }
}
