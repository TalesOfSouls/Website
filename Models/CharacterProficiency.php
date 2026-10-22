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
 * Proficiency (profession) levels of a character.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class CharacterProficiency
{
    public int $id = 0;

    public int $character = 0;

    public int $brewing = 0;

    public int $forging = 0;

    public int $enchanting = 0;

    public int $cooking = 0;

    public int $tailoring = 0;

    public int $breeding = 0;

    public int $taming = 0;

    public int $mining = 0;

    public int $woodcutting = 0;

    public int $gathering = 0;

    public int $stealing = 0;

    public int $stealth = 0;

    public int $archeology = 0;

    public int $fishing = 0;

    public int $fletching = 0;

    public int $diving = 0;

    public int $farming = 0;

    public int $endurance = 0;

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

    /**
     * Proficiencies the character has learned (name => level), highest first
     *
     * @return array<string, int>
     *
     * @since 1.0.0
     */
    public function getLearned() : array
    {
        $learned = [];
        foreach (\get_object_vars($this) as $name => $value) {
            if ($name !== 'id' && $name !== 'character' && $value > 0) {
                $learned[\ucfirst($name)] = $value;
            }
        }

        \arsort($learned);

        return $learned;
    }
}
