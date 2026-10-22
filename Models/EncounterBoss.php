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
 * Boss of an encounter.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class EncounterBoss
{
    public int $id = 0;

    public int $encounter = 0;

    public InstanceBoss $boss;

    public bool $killed = false;

    /**
     * Attempts
     *
     * @var EncounterAttempt[]
     * @since 1.0.0
     */
    public array $attempts = [];

    /**
     * Statistics of the kill (or last) attempt
     *
     * @var EncounterBossStat[]
     * @since 1.0.0
     */
    public array $stats = [];

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
        $this->boss = new NullInstanceBoss();
    }
}
