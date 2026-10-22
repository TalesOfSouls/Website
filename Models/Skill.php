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
 * Skill.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Skill
{
    public int $id = 0;

    public string $name = '';

    public string $note = '';

    /**
     * Cooldown in seconds (0 = no cooldown)
     *
     * @var ?int
     * @since 1.0.0
     */
    public ?int $cooldown = null;

    /**
     * damage, heal or utility
     *
     * @var string
     * @since 1.0.0
     */
    public string $kind = '';

    /**
     * single or aoe
     *
     * @var string
     * @since 1.0.0
     */
    public string $target = '';

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
