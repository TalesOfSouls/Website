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
 * Account.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Account
{
    public int $id = 0;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public ?\DateTime $created = null;

    public ?\DateTime $lastLogin = null;

    public int $status = AccountStatus::ACTIVE;

    public int $role = AccountRole::PLAYER;

    /**
     * Characters
     *
     * @var Character[]
     * @since 1.0.0
     */
    public array $characters = [];

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
     * Is the account part of the dev team? (developers and administrators)
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isDeveloper() : bool
    {
        return $this->role === AccountRole::DEVELOPER || $this->role === AccountRole::ADMIN;
    }

    /**
     * Is the account an administrator?
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isAdmin() : bool
    {
        return $this->role === AccountRole::ADMIN;
    }
}
