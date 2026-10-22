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
 * Milestone of the development roadmap.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Milestone
{
    public int $id = 0;

    public string $title = '';

    public string $description = '';

    /**
     * done, progress or planned
     *
     * @var string
     * @since 1.0.0
     */
    public string $state = 'planned';

    /**
     * Date as text (e.g. "Jul 2024", "TBA")
     *
     * @var string
     * @since 1.0.0
     */
    public string $when = '';

    public int $order = 0;

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
