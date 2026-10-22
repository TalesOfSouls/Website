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
 * Guild member.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class GuildMember
{
    public int $id = 0;

    public Character $character;

    public GuildRole $role;

    public GuildRank $rank;

    public int $guild = 0;

    public ?\DateTime $joined = null;

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
        $this->role = new NullGuildRole();
        $this->rank = new NullGuildRank();
    }
}
