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
 * Character.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Character
{
    public int $id = 0;

    public Account $account;

    public string $name = '';

    public ?int $title = null;

    public Server $server;

    public PlayerClass $class;

    public Race $race;

    public int $xp = 0;

    public Guild $guild;

    /**
     * Playtime in seconds
     *
     * @var int
     * @since 1.0.0
     */
    public int $playtime = 0;

    public ?\DateTime $created = null;

    /**
     * Season id of seasonal characters
     *
     * @var ?int
     * @since 1.0.0
     */
    public ?int $season = null;

    public bool $hardcore = false;

    public bool $dead = false;

    public int $itemLevel = 0;

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
        $this->account = new NullAccount();
        $this->server = new NullServer();
        $this->class = new NullPlayerClass();
        $this->race = new NullRace();
        $this->guild = new NullGuild();
    }

    /**
     * Character level (calculated from the xp)
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getLevel() : int
    {
        return PlayerLevel::levelFromXp($this->xp);
    }

    /**
     * Playtime in hours
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getPlaytimeHours() : int
    {
        return (int) \round($this->playtime / 3600);
    }
}
