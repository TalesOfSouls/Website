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
 * Dungeon, raid, world boss or PvP map.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Instance
{
    public int $id = 0;

    /**
     * Encounter type (see EncounterType)
     *
     * @var string
     * @since 1.0.0
     */
    public string $type = '';

    public string $name = '';

    public string $location = '';

    public int $size = 0;

    public PvpMode $pvpMode;

    /**
     * Bosses
     *
     * @var InstanceBoss[]
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
        $this->pvpMode = new NullPvpMode();
    }
}
