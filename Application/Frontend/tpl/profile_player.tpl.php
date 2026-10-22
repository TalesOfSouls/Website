<?php
use Application\Frontend\Chart;
use Application\Frontend\SampleAchievements;
use Application\Frontend\SampleEncounters;
use Application\Frontend\SampleSeasons;

/** @var \phpOMS\Views\View $this */
$profile    = $this->data['profile'];
$tab        = $this->data['tab'] ?? 'overview';
$encounters = $this->data['encounters'] ?? [];
$samples = $this->data['sample'] ?? ['players' => [], 'characters' => [], 'guilds' => []];

if ($profile->id === 0) {
    include __DIR__ . '/error/404.tpl.php';
    return;
}

// @todo Replace the sample values once accounts have real statistics
$player = $samples['players'][$profile->id] ?? null;

$characters = [];
foreach ($player['characters'] ?? [] as $characterId) {
    if (isset($samples['characters'][$characterId])) {
        $characters[$characterId] = $samples['characters'][$characterId];
    }
}

$achievements       = $this->data['achievements'] ?? [];
$achievementHistory = SampleAchievements::pointHistory($achievements);
$achievementPoints  = \array_sum(\array_column($achievements, 'points'));
$recentUnlocks      = \array_slice(\array_reverse($achievementHistory), 0, 5);
?>
<div class="floater">
    <div class="profile-banner">
        <p class="profile-back"><a href="/player/profile">Players</a></p>
        <div class="profile-head">
            <div class="avatar"><?= $this->printHtml(\mb_substr($profile->name, 0, 1)); ?></div>
            <div>
                <h1 class="profile-name"><?= $this->printHtml($profile->name); ?></h1>
                <p class="profile-meta">Member since <?= $player['since'] ?? '-'; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="floater page-section">
    <nav class="tabs">
        <a href="/player/profile/<?= $profile->id; ?>"<?= $tab === 'overview' ? ' class="active"' : ''; ?>>Overview</a>
        <a href="/player/profile/<?= $profile->id; ?>?tab=achievements"<?= $tab === 'achievements' ? ' class="active"' : ''; ?>>Achievements</a>
        <a href="/player/profile/<?= $profile->id; ?>?tab=seasons"<?= $tab === 'seasons' ? ' class="active"' : ''; ?>>Seasons</a>
        <?php foreach (SampleEncounters::TYPES as $type => $typeTitle) : ?>
            <a href="/player/profile/<?= $profile->id; ?>?tab=<?= $type; ?>"<?= $tab === $type ? ' class="active"' : ''; ?>><?= $typeTitle; ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="notice info">Profile statistics are sample values.</div>

<?php if ($tab === 'overview') : ?>
    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= isset($player) ? $player['playtime'] . ' h' : '-'; ?></span><span class="label">Playtime</span></div>
        <div class="stat-tile"><span class="value"><?= \number_format($achievementPoints); ?></span><span class="label">Achievement points</span></div>
        <div class="stat-tile"><span class="value"><?= $player['pvp_kills'] ?? '-'; ?></span><span class="label">PvP kills</span></div>
        <div class="stat-tile"><span class="value"><?= $player['dungeons'] ?? '-'; ?></span><span class="label">Dungeons cleared</span></div>
    </div>

    <div class="layout-halves">
        <div class="panel">
            <h2>Characters</h2>
            <table class="content-table">
                <thead><tr><th class="wf-100">Character</th><th>Class</th><th>Level</th><th>Guild</th></tr></thead>
                <tbody>
                <?php foreach ($characters as $characterId => $character) :
                    $guild = $samples['guilds'][$character['guild']] ?? null;
                ?>
                    <tr>
                        <td><a href="/player/character/profile/<?= $characterId; ?>"><?= $this->printHtml($character['name']); ?></a></td>
                        <td><?= $this->printHtml($character['class']); ?></td>
                        <td><?= $character['level']; ?></td>
                        <td><?php if ($guild !== null) : ?><a href="/guild/profile/<?= $character['guild']; ?>"><?= $this->printHtml($guild['name']); ?></a><?php else : ?>-<?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($characters)) : ?>
                    <tr><td colspan="4">No characters yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="panel">
            <h2>Recent achievements</h2>
            <dl class="stat-list">
                <?php foreach ($recentUnlocks as $unlock) : ?>
                    <dt><?= $this->printHtml($unlock['title']); ?></dt><dd><?= $unlock['date']->format('Y-m-d'); ?></dd>
                <?php endforeach; ?>
            </dl>
            <p><a href="/player/profile/<?= $profile->id; ?>?tab=achievements">All achievements</a></p>
        </div>
    </div>
<?php elseif ($tab === 'achievements') :
    // Achievement points over time (cumulative)
    $points = [['x' => 0, 'y' => 0, 'title' => 'Account created']];
    $since  = new \DateTime($player['since'] ?? '2024-07-01');
    foreach ($achievementHistory as $unlock) {
        $points[] = [
            'x'     => ($unlock['date']->getTimestamp() - $since->getTimestamp()) / 86400,
            'y'     => $unlock['total'],
            'title' => $unlock['date']->format('Y-m-d') . ': ' . $unlock['title'] . ' (+' . $unlock['points'] . ', total ' . $unlock['total'] . ')',
        ];
    }

    // the chart ends with the last unlock, a long flat tail would only hide the progression
    $days     = \max(1, (int) \ceil(\end($points)['x']));
    $maxPts   = \max(50, (int) (\ceil($achievementPoints / 100) * 100));
    $tickStep = $days <= 120 ? \max(7, (int) (\ceil($days / 6 / 7) * 7)) : \max(30, (int) (\ceil($days / 6 / 30) * 30));
    $dayTicks = \range(0, $days, $tickStep);

    $achievementChart = Chart::line(
        [['label' => \number_format($achievementPoints) . ' points', 'class' => 's-hp', 'step' => true, 'points' => $points]],
        [
            'label'   => 'Achievement points over time',
            'xMax'    => $days,
            'yMax'    => $maxPts,
            'yTicks'  => [0, $maxPts / 2, $maxPts],
            'yFormat' => fn ($v) => \number_format($v),
            'xTicks'  => $dayTicks,
            'xFormat' => fn ($v) => (clone $since)->modify('+' . (int) $v . ' days')->format($days <= 120 ? 'M j' : 'M Y'),
        ]
    );

    $maxPossible = \count($achievements) * \array_sum(SampleAchievements::TIER_POINTS);
    $completed   = \count(\array_filter($achievements, fn ($a) => $a['reached'] === 5));
    $tiers       = \array_sum(\array_column($achievements, 'reached'));

    $byCategory = [];
    foreach ($achievements as $achievement) {
        $byCategory[$achievement['category']][] = $achievement;
    }
?>
    <div class="panel">
        <h2>Achievement progression</h2>
        <?= $achievementChart; ?>
        <p class="muted chart-note">Achievement points over time. Hover a step for the unlocked achievement.</p>
    </div>

    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= \number_format($achievementPoints); ?></span><span class="label">Points of <?= \number_format($maxPossible); ?></span></div>
        <div class="stat-tile"><span class="value"><?= $tiers; ?> / <?= \count($achievements) * 5; ?></span><span class="label">Tiers reached</span></div>
        <div class="stat-tile"><span class="value"><?= $completed; ?> / <?= \count($achievements); ?></span><span class="label">Fully completed</span></div>
        <div class="stat-tile"><span class="value"><?= \round($achievementPoints / \max(1, $maxPossible) * 100); ?>%</span><span class="label">Completion</span></div>
    </div>

    <div class="panel">
        <h2>Achievements</h2>
        <div class="css-tabs achievement-tabs">
        <?php $n = 0; foreach ($byCategory as $category => $list) : ++$n;
            $categoryId = 'achievements-' . \strtolower(\preg_replace('/[^a-z0-9]+/i', '-', $category));
            $categoryReached = \array_sum(\array_column($list, 'reached'));
        ?>
            <input type="radio" name="achievement-category" id="<?= $categoryId; ?>"<?= $n === 1 ? ' checked' : ''; ?>>
            <label for="<?= $categoryId; ?>"><?= $this->printHtml($category); ?> <small class="muted"><?= $categoryReached; ?>/<?= \count($list) * 5; ?></small></label>
        <?php endforeach; ?>

        <?php foreach ($byCategory as $category => $list) : ?>
        <div class="css-tab-panel">
        <div class="achievement-list">
        <?php foreach ($list as $achievement) :
            $next      = $achievement['reached'] < 5 ? $achievement['tiers'][$achievement['reached']] : null;
            $lower     = $achievement['reached'] === 0 ? 0 : $achievement['tiers'][$achievement['reached'] - 1];
            $tierShare = $next === null ? 100 : \round(($achievement['progress'] - $lower) / \max(1, $next - $lower) * 100);
            $lastDate  = empty($achievement['unlocked']) ? null : \end($achievement['unlocked']);
        ?>
            <div class="achievement<?= $achievement['reached'] === 0 ? ' locked' : ''; ?>">
                <div class="achievement-main">
                    <span class="achievement-name"><?= $this->printHtml($achievement['name']); ?></span>
                    <span class="achievement-desc"><?= $this->printHtml($achievement['description']); ?></span>
                </div>
                <div class="achievement-tiers" title="<?= $achievement['reached']; ?> of 5 tiers reached">
                    <?php for ($t = 1; $t <= 5; ++$t) : ?>
                        <span class="tier<?= $t <= $achievement['reached'] ? ' reached' : ''; ?>"
                            title="Tier <?= SampleAchievements::romanTier($t); ?>: <?= \number_format($achievement['tiers'][$t - 1]); ?> <?= $achievement['unit']; ?><?= isset($achievement['unlocked'][$t - 1]) ? ' - ' . $achievement['unlocked'][$t - 1]->format('Y-m-d') : ''; ?>"><?= SampleAchievements::romanTier($t); ?></span>
                    <?php endfor; ?>
                </div>
                <div class="achievement-progress">
                    <span class="share-bar"><span style="width: <?= $tierShare; ?>%"></span></span>
                    <span class="achievement-count">
                        <?= \number_format($achievement['progress']); ?><?= $next !== null ? ' / ' . \number_format($next) : ''; ?> <?= $achievement['unit']; ?>
                    </span>
                </div>
                <div class="achievement-meta">
                    <span class="achievement-points"><?= $achievement['points']; ?> pts</span>
                    <span class="muted"><?= $lastDate !== null ? $lastDate->format('Y-m-d') : 'Not started'; ?></span>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php elseif ($tab === 'seasons') :
    $seasons  = $this->data['seasons'] ?? [];
    $selected = $seasons[$this->data['selectedSeason'] ?? 0] ?? null;
    $played   = \array_filter($seasons, fn ($s) => $s['played']);

    $bossesKilled = \array_sum(\array_map(fn ($s) => $s['bosses_killed'], $played));
    $bossesTotal  = \array_sum(\array_map(fn ($s) => \count($s['bosses']), $seasons));
    $bestProgress = empty($played) ? 0 : \max(\array_column($played, 'progress'));

    $bestLadder = null;
    foreach ($played as $s) {
        if ($s['ladder'] !== null && ($bestLadder === null || $s['ladder']['rank'] < $bestLadder['rank'])) {
            $bestLadder = $s['ladder'] + ['season' => $s['name']];
        }
    }

    $seasonDates = fn (array $s) : string => (new \DateTime($s['start']))->format('M Y') . ' - ' . (new \DateTime($s['end']))->format('M Y');
?>
    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= \count($played); ?> / <?= \count($seasons); ?></span><span class="label">Seasons played</span></div>
        <div class="stat-tile"><span class="value"><?= $bossesKilled; ?> / <?= $bossesTotal; ?></span><span class="label">Main bosses defeated</span></div>
        <div class="stat-tile"><span class="value"><?= $bestProgress; ?>%</span><span class="label">Best progression</span></div>
        <div class="stat-tile"><span class="value"><?= $bestLadder !== null ? '#' . \number_format($bestLadder['rank']) : '-'; ?></span><span class="label">Best ladder rank<?= $bestLadder !== null ? ' (' . $bestLadder['division'] . ')' : ''; ?></span></div>
    </div>

    <?php if ($selected !== null) : ?>
    <div class="panel season-detail">
        <h2>
            <?= $this->printHtml($selected['name']); ?>
            <?php if (SampleSeasons::isCurrent($selected['season'])) : ?>
                <span class="badge progress">Current</span>
            <?php else : ?>
                <span class="badge">Ended</span>
            <?php endif; ?>
        </h2>
        <p class="muted season-dates">
            <?= (new \DateTime($selected['start']))->format('Y-m-d'); ?> - <?= (new \DateTime($selected['end']))->format('Y-m-d'); ?>
            &middot; World dungeon: <?= $this->printHtml($selected['dungeon']); ?>
        </p>

        <?php if (!$selected['played']) : ?>
            <p><?= $this->printHtml($profile->name); ?> did not take part in this season.</p>
        <?php else : ?>
        <div class="season-grid">
            <div>
                <h3>World dungeon progression</h3>
                <div class="season-progress">
                    <span class="season-progress-value"><?= $selected['progress']; ?>%</span>
                    <span class="share-bar"><span style="width: <?= $selected['progress']; ?>%"></span></span>
                </div>

                <dl class="season-counts">
                    <dt>Zones explored</dt>
                    <dd>
                        <span class="share-bar"><span style="width: <?= \round($selected['zones_done'] / $selected['zones'] * 100); ?>%"></span></span>
                        <span><?= $selected['zones_done']; ?> / <?= $selected['zones']; ?></span>
                    </dd>
                    <dt>Elite bosses</dt>
                    <dd>
                        <span class="share-bar"><span style="width: <?= \round($selected['elites_done'] / $selected['elites'] * 100); ?>%"></span></span>
                        <span><?= $selected['elites_done']; ?> / <?= $selected['elites']; ?></span>
                    </dd>
                </dl>

                <h3>Main bosses</h3>
                <ol class="season-bosses">
                <?php foreach ($selected['boss_list'] as $boss) : ?>
                    <li class="<?= $boss['killed'] ? 'killed' : ($boss['attempts'] > 0 ? 'attempted' : 'locked'); ?>">
                        <span class="season-boss-name"><?= $this->printHtml($boss['name']); ?></span>
                        <span class="season-boss-state">
                            <?php if ($boss['killed']) : ?>
                                Defeated <?= $boss['date']->format('Y-m-d'); ?> &middot; <?= $boss['attempts']; ?> attempts
                            <?php elseif ($boss['attempts'] > 0) : ?>
                                Not defeated &middot; <?= $boss['attempts']; ?> attempts
                            <?php else : ?>
                                Not reached
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
                </ol>
                <p class="muted chart-note">Progression: zones 30%, elite bosses 30%, main bosses 40%.</p>
            </div>

            <div>
                <h3>Seasonal characters</h3>
                <table class="content-table">
                    <thead><tr><th>Character</th><th>Class</th><th>Level</th><th>Created</th></tr></thead>
                    <tbody>
                    <?php foreach ($selected['characters'] as $character) : ?>
                        <tr>
                            <td><?= $this->printHtml($character['name']); ?><?= $character['died'] ? ' <span class="badge closed">Died</span>' : ''; ?></td>
                            <td><?= $this->printHtml($character['class']); ?></td>
                            <td><?= $character['level']; ?><?= $character['level'] >= SampleSeasons::MAX_LEVEL ? ' <small class="muted">max</small>' : ''; ?></td>
                            <td><?= $character['created']->format('Y-m-d'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <h3>PvP ladder</h3>
                <?php if (!$selected['ladder_enabled']) : ?>
                    <p class="muted">This season had no PvP ladder.</p>
                <?php elseif ($selected['ladder'] === null) : ?>
                    <p class="muted">Not ranked.</p>
                <?php else : $ladder = $selected['ladder']; ?>
                    <div class="season-ladder">
                        <span class="season-division division-<?= \strtolower($ladder['division']); ?>"><?= $ladder['division']; ?></span>
                        <dl>
                            <dt><?= $ladder['final'] ? 'Final rank' : 'Current rank'; ?></dt><dd>#<?= \number_format($ladder['rank']); ?> <small class="muted">of <?= \number_format($ladder['players']); ?></small></dd>
                            <dt>Rating</dt><dd><?= \number_format($ladder['rating']); ?></dd>
                            <dt>Wins / losses</dt><dd><?= $ladder['wins']; ?> / <?= $ladder['losses']; ?> <small class="muted">(<?= \round($ladder['wins'] / \max(1, $ladder['wins'] + $ladder['losses']) * 100); ?>%)</small></dd>
                        </dl>
                    </div>
                <?php endif; ?>

                <h3>Open world event</h3>
                <p><?= $this->printHtml($selected['event']); ?>: <strong><?= $selected['events']; ?></strong></p>
                <p class="muted chart-note">Seasonal open world events are not part of the progression.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="panel">
        <h2>All seasons</h2>
        <table class="content-table encounter-table season-table">
            <thead>
                <tr>
                    <th>Season</th>
                    <th>Duration</th>
                    <th>World dungeon</th>
                    <th>Characters</th>
                    <th class="wf-100">Progression</th>
                    <th>Main bosses</th>
                    <th>PvP ladder</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($seasons as $id => $season) :
                $url = '/player/profile/' . $profile->id . '?tab=seasons&amp;season=' . $id;
            ?>
                <tr class="<?= $selected !== null && $selected['season'] === $id ? 'highlight' : ''; ?><?= $season['played'] ? '' : ' not-played'; ?>">
                    <td>
                        <a class="row-link" href="<?= $url; ?>"><?= $this->printHtml($season['name']); ?></a>
                        <?php if (SampleSeasons::isCurrent($id)) : ?><span class="badge progress">Current</span><?php endif; ?>
                    </td>
                    <td class="nowrap"><?= $seasonDates($season); ?></td>
                    <td><?= $this->printHtml($season['dungeon']); ?></td>
                <?php if ($season['played']) : ?>
                    <td><?= $this->printHtml(\implode(', ', \array_column($season['characters'], 'name'))); ?></td>
                    <td>
                        <div class="share">
                            <span class="share-bar"><span style="width: <?= $season['progress']; ?>%"></span></span>
                            <span class="share-value"><?= $season['progress']; ?>%</span>
                        </div>
                    </td>
                    <td><?= $season['bosses_killed']; ?> / <?= \count($season['bosses']); ?></td>
                    <td><?= $season['ladder'] !== null ? $season['ladder']['division'] . ' &middot; #' . \number_format($season['ladder']['rank']) : '-'; ?></td>
                <?php else : ?>
                    <td colspan="4" class="muted">Not played</td>
                <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($seasons)) : ?>
                <tr><td colspan="7">No seasons yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($tab === 'pvp') :
    $wins   = \count(\array_filter($encounters, fn ($m) => $m['success']));
    $kills  = \array_sum(\array_map(fn ($m) => $m['self']['kills'], $encounters));
    $deaths = \array_sum(\array_map(fn ($m) => $m['self']['deaths'], $encounters));
    $latest = \reset($encounters);
?>
    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= $latest ? $latest['rating_before'] + $latest['rating_change'] : '-'; ?></span><span class="label">Rating</span></div>
        <div class="stat-tile"><span class="value"><?= $wins; ?> / <?= \count($encounters) - $wins; ?></span><span class="label">Wins / losses</span></div>
        <div class="stat-tile"><span class="value"><?= empty($encounters) ? '-' : \round($wins / \count($encounters) * 100) . '%'; ?></span><span class="label">Win rate</span></div>
        <div class="stat-tile"><span class="value"><?= \number_format($kills / \max(1, $deaths), 2); ?></span><span class="label">K/D ratio</span></div>
    </div>

    <div class="panel">
        <h2>PvP matches</h2>
        <table class="content-table encounter-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="wf-100">Mode</th>
                    <th>Map</th>
                    <th>Result</th>
                    <th>Duration</th>
                    <th>Score</th>
                    <th>K / D / A</th>
                    <th>Damage</th>
                    <th>Healing</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($encounters as $match) :
                $url = '/player/profile/' . $profile->id . '/pvp/' . $match['id'];
            ?>
                <tr>
                    <td><?= $match['date']->format('Y-m-d H:i'); ?></td>
                    <td><a class="row-link" href="<?= $url; ?>"><?= $this->printHtml($match['title']); ?></a></td>
                    <td><?= $this->printHtml($match['location']); ?></td>
                    <td><span class="badge <?= $match['success'] ? 'done' : 'closed'; ?>"><?= $match['success'] ? 'Win' : 'Loss'; ?></span></td>
                    <td><?= SampleEncounters::formatDuration($match['duration']); ?></td>
                    <td><?= $match['score']['own']; ?> : <?= $match['score']['enemy']; ?></td>
                    <td><?= $match['self']['kills']; ?> / <?= $match['self']['deaths']; ?> / <?= $match['self']['assists']; ?></td>
                    <td><?= SampleEncounters::formatNumber($match['self']['damage']); ?></td>
                    <td><?= SampleEncounters::formatNumber($match['self']['healing']); ?></td>
                    <td><?= $match['rating_before'] + $match['rating_change']; ?> <span class="<?= $match['rating_change'] >= 0 ? 'rating-up' : 'rating-down'; ?>">(<?= $match['rating_change'] >= 0 ? '+' : ''; ?><?= $match['rating_change']; ?>)</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <div class="panel">
        <h2><?= SampleEncounters::TYPES[$tab]; ?></h2>
        <table class="content-table encounter-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="wf-100"><?= $tab === 'worldboss' ? 'Boss' : ($tab === 'raid' ? 'Raid' : 'Dungeon'); ?></th>
                    <th><?= $tab === 'worldboss' ? 'Location' : 'Difficulty'; ?></th>
                    <th>Character</th>
                    <th>Result</th>
                    <th>Duration</th>
                    <th><?= $tab === 'worldboss' ? 'Players' : 'Bosses'; ?></th>
                    <th>Damage</th>
                    <th>Healing</th>
                    <th>Deaths</th>
                    <th>Rank</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($encounters as $encounter) :
                $url = '/player/profile/' . $profile->id . '/' . $tab . '/' . $encounter['id'];
            ?>
                <tr>
                    <td><?= $encounter['date']->format('Y-m-d H:i'); ?></td>
                    <td><a class="row-link" href="<?= $url; ?>"><?= $this->printHtml($encounter['title']); ?></a></td>
                    <td><?= $this->printHtml($tab === 'worldboss' ? $encounter['location'] : $encounter['difficulty']); ?></td>
                    <td><?= $this->printHtml($encounter['self']['name']); ?></td>
                    <td><span class="badge <?= $encounter['success'] ? 'done' : 'closed'; ?>"><?= $encounter['success'] ? ($tab === 'dungeon' ? 'Cleared' : 'Kill') : 'Failed'; ?></span></td>
                    <td><?= SampleEncounters::formatDuration($encounter['duration']); ?></td>
                    <td><?= $tab === 'worldboss' ? \count($encounter['group']) : $encounter['killed'] . '/' . $encounter['total']; ?></td>
                    <td><?= SampleEncounters::formatNumber($encounter['self']['damage']); ?></td>
                    <td><?= SampleEncounters::formatNumber($encounter['self']['healing']); ?></td>
                    <td><?= $encounter['self']['deaths']; ?></td>
                    <td>#<?= $encounter['rank']; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($encounters)) : ?>
                <tr><td colspan="11">Nothing recorded yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>
