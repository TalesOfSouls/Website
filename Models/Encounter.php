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
 * Dungeon run, raid, world boss fight or PvP match.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Encounter
{
    public int $id = 0;

    /**
     * Encounter type (see EncounterType)
     *
     * @var string
     * @since 1.0.0
     */
    public string $type = '';

    public Instance $instance;

    public string $difficulty = '';

    public \DateTime $start;

    /**
     * Duration in seconds
     *
     * @var int
     * @since 1.0.0
     */
    public int $duration = 0;

    /**
     * Cleared / killed (PvE)
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $success = false;

    /**
     * Winning team (PvP)
     *
     * @var ?int
     * @since 1.0.0
     */
    public ?int $winner = null;

    public ?int $score1 = null;

    public ?int $score2 = null;

    /**
     * Members
     *
     * @var EncounterMember[]
     * @since 1.0.0
     */
    public array $members = [];

    /**
     * Bosses
     *
     * @var EncounterBoss[]
     * @since 1.0.0
     */
    public array $bosses = [];

    /**
     * Score progression (PvP)
     *
     * @var EncounterScore[]
     * @since 1.0.0
     */
    public array $scores = [];

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
        $this->instance = new NullInstance();
        $this->start = new \DateTime();
    }
}
