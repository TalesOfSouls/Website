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
 * Feedback type enum.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class FeedbackType extends Enum
{
    public const BUG = 'Bug';

    public const SUGGESTION = 'Suggestion';

    public const BALANCE = 'Balance';

    public const WEBSITE = 'Website';

    public const OTHER = 'Other';
}
