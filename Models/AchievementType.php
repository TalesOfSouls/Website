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
 * Achievement type.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class AchievementType
{
    public int $id = 0;

    public string $name = '';

    public string $description = '';

    public AchievementCategory $category;

    public ?int $score1 = null;

    public ?int $score2 = null;

    public ?int $score3 = null;

    public ?int $score4 = null;

    public ?int $score5 = null;

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
        $this->category = new NullAchievementCategory();
    }

    /**
     * Points per reached tier
     *
     * @var int[]
     * @since 1.0.0
     */
    public const TIER_POINTS = [10, 20, 30, 40, 50];

    /**
     * Thresholds of the tiers (ascending). Achievements without thresholds have one tier.
     *
     * @return int[]
     *
     * @since 1.0.0
     */
    public function getThresholds() : array
    {
        $thresholds = \array_values(\array_unique(\array_filter(
            [$this->score1, $this->score2, $this->score3, $this->score4, $this->score5],
            fn ($v) => $v !== null
        )));

        \sort($thresholds);

        return empty($thresholds) ? [1] : $thresholds;
    }

    /**
     * Display name (some achievements only have a description so far)
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getTitle() : string
    {
        return $this->name !== '' ? $this->name : $this->description;
    }
}
