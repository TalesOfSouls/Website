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

use phpOMS\Stdlib\Base\Enum;

/**
 * Feedback state enum.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class FeedbackState extends Enum
{
    public const OPEN = 'open';

    public const PLANNED = 'planned';

    public const PROGRESS = 'progress';

    public const DONE = 'done';

    public const CLOSED = 'closed';
}
