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
 * Event log entry of a character.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class CharacterEvent
{
    public int $id = 0;

    public int $character = 0;

    /**
     * Event type (see CharacterEventType)
     *
     * @var string
     * @since 1.0.0
     */
    public string $type = '';

    public string $title = '';

    public string $detail = '';

    public ?int $rarity = null;

    public ?int $encounter = null;

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
