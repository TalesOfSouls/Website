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
 * Character taking part in an encounter incl. statistics.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class EncounterMember
{
    public int $id = 0;

    public int $encounter = 0;

    public Character $character;

    public int $team = 1;

    public string $role = '';

    public int $damage = 0;

    public int $damageSingle = 0;

    public int $damageAoe = 0;

    public int $healing = 0;

    public int $healingSingle = 0;

    public int $healingAoe = 0;

    public int $healingReceived = 0;

    public int $damageTaken = 0;

    public int $deaths = 0;

    public int $kills = 0;

    public int $assists = 0;

    public int $objectives = 0;

    public int $casts = 0;

    public float $apm = 0.0;

    /**
     * Used casts of skills with a cooldown in %
     *
     * @var int
     * @since 1.0.0
     */
    public int $cooldownUsage = 0;

    /**
     * Share of the fight the character was casting in %
     *
     * @var int
     * @since 1.0.0
     */
    public int $active = 0;

    public ?int $ratingBefore = null;

    public ?int $ratingChange = null;

    /**
     * Skill usage
     *
     * @var EncounterMemberSkill[]
     * @since 1.0.0
     */
    public array $skills = [];

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
        $this->character = new NullCharacter();
    }
}
