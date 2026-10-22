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
 * Sample dungeon runs, raids and world boss fights.
 *
 * The data is generated deterministically from the player id, so every run always has the same
 * values and can be opened on its own detail page.
 *
 * @todo Replace with the log tables (ladder_dungeon, ladder_raid, ...) once the game servers write them
 */
final class SampleEncounters
{
    /**
     * Encounter types (url name => title)
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'dungeon'   => 'Dungeons',
        'raid'      => 'Raids',
        'worldboss' => 'World bosses',
        'pvp'       => 'PvP',
    ];

    /**
     * PvP modes: team size, score target and maps
     */
    private const PVP_MODES = [
        ['name' => 'Arena 2v2',              'size' => 2,  'target' => 3,    'unit' => 'rounds',    'maps' => ['Bloodsand Pit', 'Frozen Ring']],
        ['name' => 'Arena 3v3',              'size' => 3,  'target' => 3,    'unit' => 'rounds',    'maps' => ['Bloodsand Pit', 'Frozen Ring', 'Ruins of Vael']],
        ['name' => 'Capture the Flag',       'size' => 8,  'target' => 3,    'unit' => 'captures',  'maps' => ['Twin Towers', 'Moonwood Glade']],
        ['name' => 'Team Deathmatch',        'size' => 6,  'target' => 50,   'unit' => 'kills',     'maps' => ['Ashen Crossroads', 'Sunken Docks']],
        ['name' => 'Capture the Objectives', 'size' => 8,  'target' => 1000, 'unit' => 'points',    'maps' => ['Iron Fields', 'Styx Delta']],
        ['name' => 'Siege',                  'size' => 10, 'target' => 5,    'unit' => 'gates',     'maps' => ['Ironhold', 'Frostmere Keep']],
    ];

    private const DUNGEONS = [
        ['name' => 'Sunken Crypt',     'bosses' => ['Drowned Warden', 'Mother of Eels', 'The Pale Abbot']],
        ['name' => 'Ashen Halls',      'bosses' => ['Cinder Hound', 'Forgemaster Ulgar', 'The Ember Queen']],
        ['name' => 'Moonwood Hollow',  'bosses' => ['Thornback', 'Whispering Dryad', 'Moonfang']],
        ['name' => 'Frostmere Depths', 'bosses' => ['Ice Golem', 'Hrimthur', 'Vael the Frozen']],
        ['name' => 'Iron Deep',        'bosses' => ['Rust Crawler', 'Overseer Grom', 'The Deep Engine']],
    ];

    private const RAIDS = [
        ['name' => 'Hall of Ashes',     'bosses' => ['Pyre Sentinel', 'Twin Cinders', 'Archon of Embers', 'Ashborn Tyrant']],
        ['name' => 'Spire of the Styx', 'bosses' => ['Soul Ferryman', 'The Weeping Choir', 'Gatekeeper Morr', 'Lady of the Riverbed', 'The Styx Incarnate']],
    ];

    private const WORLD_BOSSES = [
        ['name' => 'Gorrath the Undying', 'location' => 'Ashen Wastes'],
        ['name' => 'Frost Wyrm Vael',     'location' => 'Frostmere'],
        ['name' => 'The Drowned King',    'location' => 'Port Sorrow'],
        ['name' => 'Elder Treant Ysmir',  'location' => 'Moonwood'],
    ];

    /**
     * Characters used to fill the groups
     */
    private const FILLER = [
        ['Kaelen', 'Assassin'], ['Elowen', 'Bard'], ['Draven', 'Hunter'], ['Isolde', 'Mage'],
        ['Garrick', 'Knight'], ['Nimue', 'Shaman'], ['Torvald', 'Paladin'], ['Seraphine', 'Witch Doctor'],
        ['Rurik', 'Engineer'], ['Maelis', 'Druid'], ['Oswin', 'Spellblade'], ['Vespera', 'Necromancer'],
        ['Hadrian', 'Monk'], ['Talia', 'Scout'], ['Ulric', 'Dualist'], ['Wren', 'Conjuror'],
        ['Lyra', 'Totemist'], ['Bastian', 'Priest'], ['Odessa', 'Beast Master'], ['Cassian', 'Berserker'],
        ['Mira', 'Scribe'], ['Thane', 'Tormentor'], ['Aveline', 'Shapeshifter'], ['Doran', 'Archeologist'],
    ];

    private const TANKS   = ['Paladin', 'Knight', 'Berserker', 'Monk'];
    private const HEALERS = ['Priest', 'Druid', 'Shaman', 'Bard', 'Totemist'];

    /**
     * Get all encounters of a type for a player (newest first)
     *
     * @param int    $playerId Player id
     * @param string $type     Encounter type (see TYPES)
     * @param array  $samples  Sample profiles (data/sample_profiles.php)
     *
     * @return array<int, array>
     */
    public static function forPlayer(int $playerId, string $type, array $samples) : array
    {
        if (!isset(self::TYPES[$type])) {
            return [];
        }

        \mt_srand(\crc32($playerId . '-' . $type));

        $count = ['dungeon' => 14, 'raid' => 8, 'worldboss' => 10, 'pvp' => 16][$type];
        $self  = self::getPlayerCharacter($playerId, $samples);
        $date  = new \DateTime('2026-10-08 20:15:00');

        $runs = [];
        for ($i = 1; $i <= $count; ++$i) {
            $date = (clone $date)->modify('-' . \mt_rand(1, 4) . ' days')
                ->setTime(\mt_rand(17, 22), \mt_rand(0, 11) * 5);

            $runs[$i] = $type === 'pvp'
                ? self::generatePvp($i, clone $date, $self, $samples)
                : self::generate($type, $i, clone $date, $self, $samples);
        }

        // PvP rating: chronological history ending at the current rating
        if ($type === 'pvp') {
            $rating = 1400 + $playerId % 7 * 50;
            foreach ($runs as $i => $run) {
                $runs[$i]['rating_before'] = $rating - $run['rating_change'];
                $rating                    = $runs[$i]['rating_before'];
            }
        }

        return $runs;
    }

    /**
     * Get a single encounter
     *
     * @param int    $playerId Player id
     * @param string $type     Encounter type
     * @param int    $id       Encounter id
     * @param array  $samples  Sample profiles
     *
     * @return null|array
     */
    public static function get(int $playerId, string $type, int $id, array $samples) : ?array
    {
        return self::forPlayer($playerId, $type, $samples)[$id] ?? null;
    }

    /**
     * Character of the player that takes part in the encounters
     */
    private static function getPlayerCharacter(int $playerId, array $samples) : array
    {
        $characterId = $samples['players'][$playerId]['characters'][0] ?? 0;

        if (isset($samples['characters'][$characterId])) {
            return [
                'id'    => $characterId,
                'name'  => $samples['characters'][$characterId]['name'],
                'class' => $samples['characters'][$characterId]['class'],
            ];
        }

        return ['id' => 0, 'name' => $samples['players'][$playerId]['name'] ?? 'Unknown', 'class' => 'Paladin'];
    }

    private static function role(string $class) : string
    {
        if (\in_array($class, self::TANKS)) {
            return 'Tank';
        }

        return \in_array($class, self::HEALERS) ? 'Healer' : 'DPS';
    }

    /**
     * Build the group: the player's character + random other characters
     */
    private static function group(array $self, int $size, array $samples) : array
    {
        $pool = [];
        foreach ($samples['characters'] as $id => $character) {
            if ($character['name'] !== $self['name']) {
                $pool[] = ['id' => $id, 'name' => $character['name'], 'class' => $character['class']];
            }
        }

        foreach (self::FILLER as $filler) {
            $pool[] = ['id' => 0, 'name' => $filler[0], 'class' => $filler[1]];
        }

        // deterministic shuffle based on the current mt_rand state
        for ($i = \count($pool) - 1; $i > 0; --$i) {
            $j        = \mt_rand(0, $i);
            $tmp      = $pool[$i];
            $pool[$i] = $pool[$j];
            $pool[$j] = $tmp;
        }

        $group = \array_merge([$self], \array_slice($pool, 0, $size - 1));

        foreach ($group as $i => $member) {
            $group[$i]['role'] = self::role($member['class']);
        }

        return $group;
    }

    /**
     * Generate the attempts for a boss
     *
     * @param int  $maxAttempts Maximum amount of attempts
     * @param bool $kill        Boss is killed in the last attempt
     */
    private static function attempts(int $maxAttempts, bool $kill) : array
    {
        // most bosses die after a few attempts, some take many evenings
        $count    = \min(\mt_rand(1, $maxAttempts), \mt_rand(1, $maxAttempts));
        $hp       = (float) \mt_rand(55, 90);
        $attempts = [];

        for ($a = 1; $a <= $count; ++$a) {
            $isKill = $kill && $a === $count;
            $hpLeft = $isKill ? 0 : (int) \max(2, \round($hp));

            // the group improves over time, but not every attempt is better than the last one
            $hp = \max(2.0, $hp * \mt_rand(78, 97) / 100 + \mt_rand(-2, 6));

            $attempts[] = [
                'no'       => $a,
                'duration' => $isKill ? \mt_rand(240, 420) : \mt_rand(90, 360),
                'hp_left'  => $hpLeft,
                'kill'     => $isKill,
            ];
        }

        return $attempts;
    }

    /**
     * Generate one encounter incl. group, bosses and statistics
     */
    private static function generate(string $type, int $id, \DateTime $date, array $self, array $samples) : array
    {
        if ($type === 'dungeon') {
            $def        = self::DUNGEONS[\mt_rand(0, \count(self::DUNGEONS) - 1)];
            $level      = \mt_rand(2, 15);
            $difficulty = \mt_rand(0, 2) === 0 ? 'Heroic' : 'Timed +' . $level;
            $size       = 5;
            $success    = \mt_rand(1, 100) <= 85;
            $maxTries   = 2;
            $bossNames  = $def['bosses'];
            $title      = $def['name'];
            $location   = '';
        } elseif ($type === 'raid') {
            $def        = self::RAIDS[\mt_rand(0, \count(self::RAIDS) - 1)];
            $difficulty = \mt_rand(0, 1) === 0 ? 'Normal' : 'Heroic';
            $size       = 10;
            $success    = \mt_rand(1, 100) <= 65;
            $maxTries   = 18;
            $bossNames  = $def['bosses'];
            $title      = $def['name'];
            $location   = '';
        } else {
            $def        = self::WORLD_BOSSES[\mt_rand(0, \count(self::WORLD_BOSSES) - 1)];
            $difficulty = 'World';
            $size       = \mt_rand(18, 40);
            $success    = \mt_rand(1, 100) <= 90;
            $maxTries   = 1;
            $bossNames  = [$def['name']];
            $title      = $def['name'];
            $location   = $def['location'];
        }

        // Bosses: on a failed run the group stops at a random boss
        $lastBoss = $success ? \count($bossNames) - 1 : \mt_rand(0, \count($bossNames) - 1);
        $bosses   = [];
        $duration = 0;

        foreach ($bossNames as $b => $bossName) {
            if ($b > $lastBoss) {
                $bosses[] = ['name' => $bossName, 'attempts' => [], 'killed' => false];
                continue;
            }

            $kill     = $success || $b < $lastBoss;
            $attempts = self::attempts($maxTries + ($kill ? 0 : 1), $kill);
            $bosses[] = ['name' => $bossName, 'attempts' => $attempts, 'killed' => $kill];

            foreach ($attempts as $attempt) {
                $duration += $attempt['duration'];
            }

            // trash and walking time between bosses
            $duration += $type === 'worldboss' ? 0 : \mt_rand(120, 420);
        }

        // Group statistics
        $group = self::group($self, $size, $samples);
        foreach ($group as $i => $member) {
            $dps = match ($member['role']) {
                'Tank'   => \mt_rand(1200, 2000),
                'Healer' => \mt_rand(300, 700),
                default  => \mt_rand(2500, 4500),
            };

            $hps = match ($member['role']) {
                'Tank'   => \mt_rand(50, 200),
                'Healer' => \mt_rand(2000, 3500),
                default  => \mt_rand(0, 120),
            };

            $group[$i]['damage']  = $dps * $duration;
            $group[$i]['healing'] = $hps * $duration;
            $group[$i]['dps']     = $dps;
            $group[$i]['hps']     = $hps;
            $group[$i]['deaths']  = \mt_rand(0, 100) < 70 ? 0 : \mt_rand(1, $success ? 2 : 4);
        }

        $group = self::detailedStats($group, $duration);

        // Damage per boss (kill attempt or last attempt) per member
        foreach ($bosses as $b => $boss) {
            $last = \end($boss['attempts']);
            if ($last === false) {
                continue;
            }

            $metrics = ['damage' => [], 'healing' => [], 'received' => [], 'taken' => []];
            foreach ($group as $i => $member) {
                $factor = $last['duration'] / \max(1, $duration);

                $metrics['damage'][$i]   = (int) ($member['dps'] * $last['duration'] * (\mt_rand(85, 115) / 100));
                $metrics['healing'][$i]  = (int) ($member['healing'] * $factor * (\mt_rand(85, 115) / 100));
                $metrics['received'][$i] = (int) ($member['healing_received'] * $factor * (\mt_rand(85, 115) / 100));
                $metrics['taken'][$i]    = (int) ($member['damage_taken'] * $factor * (\mt_rand(85, 115) / 100));
            }

            $bosses[$b] += $metrics;
        }

        $killed = \count(\array_filter($bosses, fn ($boss) => $boss['killed']));

        $byDamage = $group;
        \usort($byDamage, fn ($a, $b) => $b['damage'] <=> $a['damage']);
        $rank = 1 + (int) \array_search($self['name'], \array_column($byDamage, 'name'));

        return [
            'id'         => $id,
            'type'       => $type,
            'title'      => $title,
            'location'   => $location,
            'difficulty' => $difficulty,
            'date'       => $date,
            'duration'   => $duration,
            'success'    => $success,
            'killed'     => $killed,
            'total'      => \count($bosses),
            'bosses'     => $bosses,
            'group'      => $group,
            'self'       => $group[0],
            'rank'       => $rank,
        ];
    }

    /**
     * Generate a PvP match incl. both teams, score progression and rating change
     */
    private static function generatePvp(int $id, \DateTime $date, array $self, array $samples) : array
    {
        $mode = self::PVP_MODES[\mt_rand(0, \count(self::PVP_MODES) - 1)];
        $map  = $mode['maps'][\mt_rand(0, \count($mode['maps']) - 1)];
        $size = $mode['size'];
        $win  = \mt_rand(1, 100) <= 55;

        // both teams from one shuffled pool, so nobody plays on both sides
        $players = self::group($self, $size * 2, $samples);
        $teams   = [
            'own'   => \array_slice($players, 0, $size),
            'enemy' => \array_slice($players, $size, $size),
        ];

        // Score progression: events until one team reaches the target
        $target   = $mode['target'];
        $step     = \max(1, (int) \round($target / 25));
        $score    = ['own' => 0, 'enemy' => 0];
        $winner   = $win ? 'own' : 'enemy';
        $loser    = $win ? 'enemy' : 'own';
        $time     = 0;
        $timeline = [['time' => 0, 'own' => 0, 'enemy' => 0]];

        while ($score[$winner] < $target) {
            $time += \mt_rand(20, $target <= 5 ? 180 : 60);

            // the winning team scores a bit more often
            $team = \mt_rand(1, 100) <= 58 ? $winner : $loser;
            if ($team === $loser && $score[$loser] + $step >= $target) {
                $team = $winner;
            }

            $score[$team] = \min($target, $score[$team] + $step);
            $timeline[]   = ['time' => $time, 'own' => $score['own'], 'enemy' => $score['enemy']];
        }

        $duration = $time + \mt_rand(5, 30);

        // Player statistics
        foreach ($teams as $side => $members) {
            $won = ($side === 'own') === $win;

            foreach ($members as $i => $member) {
                $dps = match ($member['role']) {
                    'Tank'   => \mt_rand(900, 1600),
                    'Healer' => \mt_rand(250, 600),
                    default  => \mt_rand(1800, 3200),
                };

                $hps = $member['role'] === 'Healer' ? \mt_rand(1500, 2800) : \mt_rand(0, 150);

                $kills  = \mt_rand($won ? 2 : 0, $won ? 12 : 8) * ($member['role'] === 'Healer' ? 0.4 : 1);
                $deaths = \mt_rand($won ? 0 : 2, $won ? 6 : 10);

                $teams[$side][$i] += [
                    'kills'      => (int) \round($kills),
                    'deaths'     => $deaths,
                    'assists'    => \mt_rand(1, 14),
                    'damage'     => $dps * $duration,
                    'healing'    => $hps * $duration,
                    'dps'        => $dps,
                    'hps'        => $hps,
                    'objectives' => $mode['unit'] === 'kills' ? 0 : \mt_rand(0, 4),
                ];
            }
        }

        $teams['own']   = self::detailedStats($teams['own'], $duration);
        $teams['enemy'] = self::detailedStats($teams['enemy'], $duration);

        $ratingBefore = \mt_rand(1350, 1900);
        $ratingChange = ($win ? 1 : -1) * \mt_rand(8, 24);

        $all = \array_merge($teams['own'], $teams['enemy']);
        \usort($all, fn ($a, $b) => $b['damage'] <=> $a['damage']);

        return [
            'id'            => $id,
            'type'          => 'pvp',
            'title'         => $mode['name'],
            'location'      => $map,
            'difficulty'    => $mode['name'],
            'unit'          => $mode['unit'],
            'target'        => $target,
            'date'          => $date,
            'duration'      => $duration,
            'success'       => $win,
            'score'         => $score,
            'timeline'      => $timeline,
            'teams'         => $teams,
            'group'         => $teams['own'],
            'self'          => $teams['own'][0],
            'rank'          => 1 + (int) \array_search($self['name'], \array_column($all, 'name')),
            'rating_before' => $ratingBefore,
            'rating_change' => $ratingChange,
        ];
    }

    /**
     * Skills per role: name, cooldown in seconds (0 = filler on the global cooldown),
     * kind (damage, heal, utility) and target (single, aoe)
     */
    private const SKILLS = [
        'DPS' => [
            ['name' => 'Strike',        'cd' => 0,   'kind' => 'damage',  'target' => 'single'],
            ['name' => 'Rend',          'cd' => 12,  'kind' => 'damage',  'target' => 'single'],
            ['name' => 'Execute',       'cd' => 30,  'kind' => 'damage',  'target' => 'single'],
            ['name' => 'Cleave',        'cd' => 8,   'kind' => 'damage',  'target' => 'aoe'],
            ['name' => 'Whirlwind',     'cd' => 20,  'kind' => 'damage',  'target' => 'aoe'],
            ['name' => 'Battle Fury',   'cd' => 90,  'kind' => 'utility', 'target' => 'single'],
        ],
        'Tank' => [
            ['name' => 'Shield Bash',   'cd' => 0,   'kind' => 'damage',  'target' => 'single'],
            ['name' => 'Taunt',         'cd' => 8,   'kind' => 'utility', 'target' => 'single'],
            ['name' => 'Thunder Clap',  'cd' => 10,  'kind' => 'damage',  'target' => 'aoe'],
            ['name' => 'Consecrate',    'cd' => 16,  'kind' => 'damage',  'target' => 'aoe'],
            ['name' => 'Shield Wall',   'cd' => 120, 'kind' => 'utility', 'target' => 'single'],
            ['name' => 'Second Wind',   'cd' => 60,  'kind' => 'heal',    'target' => 'single'],
        ],
        'Healer' => [
            ['name' => 'Mend',          'cd' => 0,   'kind' => 'heal',    'target' => 'single'],
            ['name' => 'Renew',         'cd' => 10,  'kind' => 'heal',    'target' => 'single'],
            ['name' => 'Healing Circle','cd' => 15,  'kind' => 'heal',    'target' => 'aoe'],
            ['name' => 'Divine Hymn',   'cd' => 180, 'kind' => 'heal',    'target' => 'aoe'],
            ['name' => 'Smite',         'cd' => 6,   'kind' => 'damage',  'target' => 'single'],
            ['name' => 'Holy Nova',     'cd' => 12,  'kind' => 'damage',  'target' => 'aoe'],
        ],
    ];

    /**
     * Add single target / aoe split, incoming healing & damage and the skill usage to every member
     *
     * @param array $group    Members incl. damage, healing, role
     * @param int   $duration Duration in seconds
     *
     * @return array
     */
    private static function detailedStats(array $group, int $duration) : array
    {
        $totalHealing = \array_sum(\array_column($group, 'healing'));

        // incoming healing and damage are distributed by role (tanks take & receive the most)
        $weights = [];
        foreach ($group as $i => $member) {
            $weights[$i] = ($member['role'] === 'Tank' ? 4.0 : 1.0) * \mt_rand(70, 130) / 100;
        }

        $weightSum = \array_sum($weights);

        foreach ($group as $i => $member) {
            $aoeShare  = $member['role'] === 'DPS' ? \mt_rand(15, 60) : \mt_rand(20, 50);
            $aoeHeal   = $member['role'] === 'Healer' ? \mt_rand(25, 60) : \mt_rand(0, 20);

            $group[$i]['damage_aoe']       = (int) ($member['damage'] * $aoeShare / 100);
            $group[$i]['damage_single']    = $member['damage'] - $group[$i]['damage_aoe'];
            $group[$i]['healing_aoe']      = (int) ($member['healing'] * $aoeHeal / 100);
            $group[$i]['healing_single']   = $member['healing'] - $group[$i]['healing_aoe'];
            $group[$i]['healing_received'] = (int) ($totalHealing * $weights[$i] / $weightSum);
            $group[$i]['damage_taken']     = (int) ($group[$i]['healing_received'] * \mt_rand(95, 125) / 100);

            $group[$i] += self::skillUsage($group[$i], $duration);
        }

        return $group;
    }

    /**
     * Skill usage of a member: casts compared to the possible casts within the duration
     *
     * @param array $member   Member incl. role, damage & healing split
     * @param int   $duration Duration in seconds
     *
     * @return array{skills:array, casts:int, apm:float, cooldown_usage:int, active:int}
     */
    private static function skillUsage(array $member, int $duration) : array
    {
        $gcd        = 1.5;
        $skillDefs  = self::SKILLS[$member['role']] ?? self::SKILLS['DPS'];
        $efficiency = \mt_rand(62, 98); // how well the player uses the available time

        // amount per kind/target that is distributed over the matching skills
        $pools = [
            'damage-single' => $member['damage_single'],
            'damage-aoe'    => $member['damage_aoe'],
            'heal-single'   => $member['healing_single'],
            'heal-aoe'      => $member['healing_aoe'],
        ];

        $skills     = [];
        $casts      = 0;
        $cdPossible = 0;
        $cdCasts    = 0;
        $busy       = 0.0;

        foreach ($skillDefs as $def) {
            if ($def['cd'] > 0) {
                $possible = (int) \floor($duration / $def['cd']) + 1;
                $used     = (int) \round($possible * \min(100, $efficiency + \mt_rand(-15, 10)) / 100);
                $used     = \max(0, \min($possible, $used));

                $cdPossible += $possible;
                $cdCasts    += $used;
            } else {
                $possible = 0; // filler, calculated below from the remaining time
                $used     = 0;
            }

            $busy    += $used * $gcd;
            $skills[] = $def + ['possible' => $possible, 'casts' => $used, 'amount' => 0];
        }

        // the filler fills the remaining (used) time
        foreach ($skills as $s => $skill) {
            if ($skill['cd'] === 0) {
                $possible = (int) \max(1, \floor(($duration - $busy) / $gcd));
                $skills[$s]['possible'] = $possible;
                $skills[$s]['casts']    = (int) \round($possible * $efficiency / 100);
            }
        }

        // distribute the damage / healing over the skills, weighted by casts and cooldown
        foreach ($pools as $key => $amount) {
            [$kind, $target] = \explode('-', $key);

            $weights = [];
            foreach ($skills as $s => $skill) {
                if ($skill['kind'] === $kind && $skill['target'] === $target && $skill['casts'] > 0) {
                    $weights[$s] = $skill['casts'] * (1 + $skill['cd'] / 6);
                }
            }

            $sum = \array_sum($weights);
            foreach ($weights as $s => $weight) {
                $skills[$s]['amount'] = (int) ($amount * $weight / $sum);
            }
        }

        $casts = \array_sum(\array_column($skills, 'casts'));

        return [
            'skills'         => $skills,
            'casts'          => $casts,
            'apm'            => \round($casts / \max(1, $duration / 60), 1),
            'cooldown_usage' => $cdPossible > 0 ? (int) \round($cdCasts / $cdPossible * 100) : 100,
            'active'         => (int) \min(100, \round($casts * $gcd / \max(1, $duration) * 100)),
        ];
    }

    /**
     * Format seconds as 1:05:30 / 05:30
     */
    public static function formatDuration(int $seconds) : string
    {
        $h = \intdiv($seconds, 3600);
        $m = \intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h > 0 ? \sprintf('%d:%02d:%02d', $h, $m, $s) : \sprintf('%02d:%02d', $m, $s);
    }

    /**
     * Format large numbers (12.4M, 830K)
     */
    public static function formatNumber(int|float $value) : string
    {
        if ($value >= 1000000) {
            return \number_format($value / 1000000, 1) . 'M';
        }

        if ($value >= 1000) {
            return \number_format($value / 1000, 1) . 'K';
        }

        return (string) (int) $value;
    }
}
