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
 * Xp needed per level.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class PlayerLevel
{
    /**
     * Level
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * Xp needed for the next level (stored with thousands separators)
     *
     * @var string
     * @since 1.0.0
     */
    public string $xp = '';

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
     * Xp needed per level (level => xp)
     *
     * @var null|array<int, int>
     * @since 1.0.0
     */
    private static ?array $xpTable = null;

    /**
     * Xp needed for the next level
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getXp() : int
    {
        return (int) \preg_replace('/\D/', '', $this->xp);
    }

    /**
     * Calculate the level from the total xp
     *
     * @param int $xp Total xp
     *
     * @return int
     *
     * @since 1.0.0
     */
    public static function levelFromXp(int $xp) : int
    {
        if (self::$xpTable === null) {
            self::$xpTable = [];

            /** @var PlayerLevel[] $levels */
            $levels = PlayerLevelMapper::getAll()->executeGetArray();
            foreach ($levels as $level) {
                self::$xpTable[$level->id] = $level->getXp();
            }

            \ksort(self::$xpTable);
        }

        $level = 1;
        foreach (self::$xpTable as $l => $needed) {
            if ($xp < $needed) {
                break;
            }

            $xp   -= $needed;
            $level = $l + 1;
        }

        return \min($level, \max(1, \count(self::$xpTable)));
    }
}
