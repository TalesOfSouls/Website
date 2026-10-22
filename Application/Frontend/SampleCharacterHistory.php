<?php
/**
 * Tales of Souls
 *
 * PHP Version 8.2
 *
 * @package   Application\Frontend
 * @version   1.0.0
 */
declare(strict_types=1);

namespace Application\Frontend;

/**
 * Sample level progression and event log of a character.
 *
 * Generated deterministically from the character id.
 *
 * @todo Replace with the character_log / log_* tables once the game servers write them
 */
final class SampleCharacterHistory
{
    /**
     * Event types (key => title)
     *
     * @var array<string, string>
     */
    public const EVENT_TYPES = [
        'level'       => 'Level up',
        'profession'  => 'Profession',
        'dungeon'     => 'Dungeon',
        'raid'        => 'Raid',
        'worldboss'   => 'World boss',
        'pvp'         => 'PvP',
        'achievement' => 'Achievement',
        'loot'        => 'Loot',
    ];

    private const PROFESSIONS = ['Forging', 'Mining', 'Cooking', 'Tailoring', 'Fishing', 'Brewing', 'Enchanting', 'Woodcutting'];

    private const ACHIEVEMENTS = ['Loot Goblin', 'Delver', 'The Culling', 'Gold Digger', 'Slayer', 'Full Bling', 'No lifer'];

    private const LOOT = [
        ['Steel Longsword', 'blue'], ['Runed Helmet', 'yellow'], ['Band of Ashes', 'orange'],
        ['Dragonscale Boots', 'orange'], ['Cloak of the Styx', 'yellow'], ['Shard of the Styx', 'orange'],
        ['Moonstone Amulet', 'blue'], ['Girdle of the Tyrant', 'yellow'],
    ];

    /**
     * Level progression: level reached after x hours of playtime
     *
     * @param int    $characterId Character id
     * @param int    $level       Current level
     * @param int    $playtime    Playtime in hours
     * @param string $created     Creation date (Y-m-d)
     *
     * @return array<int, array{level:int, hours:float, date:\DateTime}>
     */
    public static function levelProgression(int $characterId, int $level, int $playtime, string $created) : array
    {
        \mt_srand(\crc32('levels-' . $characterId));

        // later levels take longer
        $weights = [];
        for ($l = 2; $l <= $level; ++$l) {
            $weights[$l] = $l ** 1.35 * \mt_rand(80, 120) / 100;
        }

        $sum = \max(1, \array_sum($weights));

        // characters below the max level are still leveling, the rest of the playtime is end game
        $levelingTime = $level >= 99 ? $playtime * 0.55 : $playtime * 0.92;

        $start = new \DateTime($created . ' 18:00:00');
        $days  = \max(30, (int) $start->diff(new \DateTime('2026-10-08'))->days);
        $hoursPerDay = $playtime / $days;

        $progression = [['level' => 1, 'hours' => 0.0, 'date' => clone $start]];
        $hours       = 0.0;

        foreach ($weights as $l => $weight) {
            $hours += $levelingTime * $weight / $sum;

            $progression[] = [
                'level' => $l,
                'hours' => \round($hours, 1),
                'date'  => (clone $start)->modify('+' . (int) ($hours / \max(0.1, $hoursPerDay) * 24 * 60) . ' minutes'),
            ];
        }

        return $progression;
    }

    /**
     * Professions of a character (name => level)
     *
     * @param int $characterId Character id
     *
     * @return array<string, int>
     */
    public static function professions(int $characterId, int $level) : array
    {
        \mt_srand(\crc32('professions-' . $characterId));

        $names = self::PROFESSIONS;
        for ($i = \count($names) - 1; $i > 0; --$i) {
            $j         = \mt_rand(0, $i);
            [$names[$i], $names[$j]] = [$names[$j], $names[$i]];
        }

        $professions = [];
        foreach (\array_slice($names, 0, 3) as $i => $name) {
            $professions[$name] = (int) \min(400, \max(10, $level * \mt_rand(200, 420) / 100 - $i * 40));
        }

        return $professions;
    }

    /**
     * Event log of a character (newest first)
     *
     * @param int   $characterId Character id
     * @param array $character   Sample character
     * @param array $samples     Sample profiles
     *
     * @return array<int, array{date:\DateTime, type:string, title:string, detail:string, link:string}>
     */
    public static function events(int $characterId, array $character, array $samples) : array
    {
        $player  = $samples['players'][$character['player']] ?? null;
        $created = $player['since'] ?? '2024-07-01';
        $events  = [];

        // Level ups
        $progression = self::levelProgression($characterId, $character['level'], $character['playtime'], $created);
        foreach ($progression as $step) {
            if ($step['level'] === 1) {
                $events[] = self::event($step['date'], 'level', 'Character created', $character['class'] . ' on ' . $character['server']);
                continue;
            }

            $events[] = self::event($step['date'], 'level', 'Reached level ' . $step['level'], 'after ' . \number_format($step['hours'], 1) . ' h playtime');
        }

        $first = $progression[0]['date'];
        $last  = new \DateTime('2026-10-08 22:00:00');
        $span  = \max(1, $last->getTimestamp() - $first->getTimestamp());

        \mt_srand(\crc32('events-' . $characterId));

        $randomDate = fn (float $from = 0.0) => (new \DateTime())->setTimestamp(
            $first->getTimestamp() + (int) ($span * ($from + (1 - $from) * \mt_rand(0, 1000) / 1000))
        );

        // Profession milestones every 25 levels
        foreach (self::professions($characterId, $character['level']) as $name => $professionLevel) {
            $milestones = \range(25, \max(25, $professionLevel), 25);
            foreach ($milestones as $i => $milestone) {
                if ($milestone > $professionLevel) {
                    break;
                }

                $date = (new \DateTime())->setTimestamp(
                    $first->getTimestamp() + (int) ($span * ($i + 1) / (\count($milestones) + 1)) + \mt_rand(0, 86400 * 5)
                );

                $events[] = self::event($date, 'profession', $name . ' level ' . $milestone, 'Profession level up');
            }
        }

        // Encounters of the player's main character (linked to their detail pages)
        if ($player !== null) {
            foreach (SampleEncounters::TYPES as $type => $typeTitle) {
                foreach (SampleEncounters::forPlayer($character['player'], $type, $samples) as $encounter) {
                    if ($encounter['self']['name'] !== $character['name']) {
                        continue;
                    }

                    $link = '/player/profile/' . $character['player'] . '/' . $type . '/' . $encounter['id'];

                    [$title, $detail] = match ($type) {
                        'dungeon'   => [($encounter['success'] ? 'Cleared ' : 'Failed ') . $encounter['title'], $encounter['difficulty'] . ' · ' . SampleEncounters::formatDuration($encounter['duration'])],
                        'raid'      => [$encounter['title'] . ' ' . $encounter['killed'] . '/' . $encounter['total'], $encounter['difficulty'] . ' · ' . SampleEncounters::formatDuration($encounter['duration'])],
                        'worldboss' => [($encounter['success'] ? 'Defeated ' : 'Fought ') . $encounter['title'], $encounter['location'] . ' · ' . \count($encounter['group']) . ' players'],
                        default     => [($encounter['success'] ? 'Won ' : 'Lost ') . $encounter['title'], $encounter['location'] . ' · ' . $encounter['score']['own'] . ' : ' . $encounter['score']['enemy']
                            . ' · rating ' . ($encounter['rating_change'] >= 0 ? '+' : '') . $encounter['rating_change']],
                    };

                    $events[] = self::event($encounter['date'], $type, $title, $detail, $link);
                }
            }
        }

        // Achievements & loot
        foreach (self::ACHIEVEMENTS as $i => $achievement) {
            if ($character['level'] < 30 && $i > 2) {
                break;
            }

            $events[] = self::event($randomDate(0.3), 'achievement', 'Achievement: ' . $achievement, 'Tier ' . \mt_rand(1, 5));
        }

        $lootCount = (int) ($character['level'] / 8);
        for ($i = 0; $i < $lootCount; ++$i) {
            $item     = self::LOOT[\mt_rand(0, \count(self::LOOT) - 1)];
            $events[] = self::event($randomDate(0.5), 'loot', 'Obtained ' . $item[0], \ucfirst($item[1]) . ' item', '', $item[1]);
        }

        \usort($events, fn ($a, $b) => $b['date'] <=> $a['date']);

        return $events;
    }

    private static function event(\DateTime $date, string $type, string $title, string $detail, string $link = '', string $rarity = '') : array
    {
        return [
            'date'   => $date,
            'type'   => $type,
            'title'  => $title,
            'detail' => $detail,
            'link'   => $link,
            'rarity' => $rarity,
        ];
    }
}
