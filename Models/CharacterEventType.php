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
 * Character event type enum.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class CharacterEventType extends Enum
{
    public const LEVEL = 'level';

    public const PROFESSION = 'profession';

    public const DUNGEON = 'dungeon';

    public const RAID = 'raid';

    public const WORLDBOSS = 'worldboss';

    public const PVP = 'pvp';

    public const ACHIEVEMENT = 'achievement';

    public const LOOT = 'loot';
}
