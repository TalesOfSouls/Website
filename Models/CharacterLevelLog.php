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
 * Level up of a character.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class CharacterLevelLog
{
    public int $id = 0;

    public int $character = 0;

    public int $level = 0;

    /**
     * Playtime in hours when the level was reached
     *
     * @var float
     * @since 1.0.0
     */
    public float $playtime = 0.0;

    public \DateTime $datetime;

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
        $this->datetime = new \DateTime();
    }
}
