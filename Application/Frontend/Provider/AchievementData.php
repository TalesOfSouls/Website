<?php
/**
 * Tales of Souls
 *
 * PHP Version 8.2
 *
 * @package   Application\Frontend\Provider
 * @version   1.0.0
 */
declare(strict_types=1);

namespace Application\Frontend\Provider;

use Models\AccountAchievementMapper;
use Models\AccountAchievementTierMapper;
use Models\AchievementType;
use Models\AchievementTypeMapper;

/**
 * Achievements of an account
 *
 * @package Application\Frontend\Provider
 * @since   1.0.0
 */
final class AchievementData extends DataAbstract
{
    /**
     * All achievements incl. the progress of an account
     *
     * @param int $account Account id
     *
     * @return array<int, array{id:int, name:string, category:string, description:string, tiers:int[], progress:int, reached:int, points:int, unlocked:array<int, \DateTime>}>
     *
     * @since 1.0.0
     */
    public function forAccount(int $account) : array
    {
        /** @var AchievementType[] $types */
        $types = AchievementTypeMapper::getAll()->with('category')->executeGetArray();

        $progress = [];
        foreach (AccountAchievementMapper::getAll()->where('account', $account)->executeGetArray() as $achievement) {
            $progress[$achievement->type->id] = $achievement->score;
        }

        $unlocked = [];
        foreach (AccountAchievementTierMapper::getAll()->where('account', $account)->executeGetArray() as $tier) {
            $unlocked[$tier->type][$tier->tier - 1] = $tier->datetime;
        }

        $achievements = [];
        foreach ($types as $type) {
            $reached = \count($unlocked[$type->id] ?? []);

            $achievements[] = [
                'id'          => $type->id,
                'name'        => $type->getTitle(),
                'category'    => $type->category->name !== '' ? $type->category->name : 'Other',
                'description' => $type->name !== '' ? $type->description : '',
                'tiers'       => $type->getThresholds(),
                'progress'    => $progress[$type->id] ?? 0,
                'reached'     => $reached,
                'points'      => \array_sum(\array_slice(AchievementType::TIER_POINTS, 0, $reached)),
                'unlocked'    => $unlocked[$type->id] ?? [],
            ];
        }

        return $achievements;
    }

    /**
     * Achievement point history (cumulative), oldest first
     *
     * @param array $achievements Result of forAccount()
     *
     * @return array<int, array{date:\DateTime, points:int, title:string, total:int}>
     *
     * @since 1.0.0
     */
    public static function pointHistory(array $achievements) : array
    {
        $unlocks = [];
        foreach ($achievements as $achievement) {
            foreach ($achievement['unlocked'] as $tier => $date) {
                $unlocks[] = [
                    'date'   => $date,
                    'points' => AchievementType::TIER_POINTS[$tier] ?? 0,
                    'title'  => $achievement['name'] . ' ' . self::romanTier($tier + 1),
                ];
            }
        }

        \usort($unlocks, fn ($a, $b) => $a['date'] <=> $b['date']);

        $sum = 0;
        foreach ($unlocks as $i => $unlock) {
            $sum                 += $unlock['points'];
            $unlocks[$i]['total'] = $sum;
        }

        return $unlocks;
    }

    /**
     * Tier as roman number
     *
     * @param int $tier Tier (1-5)
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function romanTier(int $tier) : string
    {
        return ['I', 'II', 'III', 'IV', 'V'][$tier - 1] ?? (string) $tier;
    }
}
