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
 * Guild.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Guild
{
    public int $id = 0;

    public Server $server;

    public string $name = '';

    public string $tag = '';

    public ?int $status = null;

    public bool $wvw = false;

    public bool $pvp = false;

    public bool $raid = false;

    public bool $dungeon = false;

    public bool $pve = false;

    public ?int $seriousness = null;

    public string $description = '';

    /**
     * Founding date (reserved guild names have no date)
     *
     * @var ?\DateTime
     * @since 1.0.0
     */
    public ?\DateTime $created = null;

    public bool $recruiting = false;

    /**
     * Members
     *
     * @var GuildMember[]
     * @since 1.0.0
     */
    public array $members = [];

    /**
     * Ranks
     *
     * @var GuildRank[]
     * @since 1.0.0
     */
    public array $ranks = [];

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
        $this->server = new NullServer();
    }

    /**
     * Focus of the guild (e.g. Raids, PvP)
     *
     * @return string[]
     *
     * @since 1.0.0
     */
    public function getFocus() : array
    {
        return \array_keys(\array_filter([
            'World vs. World' => $this->wvw,
            'PvP'             => $this->pvp,
            'Raids'           => $this->raid,
            'Dungeons'        => $this->dungeon,
            'PvE'             => $this->pve,
        ]));
    }
}
