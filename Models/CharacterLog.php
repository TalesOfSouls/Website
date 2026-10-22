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
 * Statistics of a character.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class CharacterLog
{
    public int $id = 0;

    public int $character = 0;

    public int $gold = 0;

    public int $playtime = 0;

    public int $chat = 0;

    public int $mobsKilled = 0;

    public int $playersKilled = 0;

    public int $dungeons = 0;

    public int $dungeonsSuccess = 0;

    public int $dungeonsQuit = 0;

    public int $raids = 0;

    public int $raidsSuccess = 0;

    public int $raidsQuit = 0;

    public int $pvp = 0;

    public int $pvpQuit = 0;

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
