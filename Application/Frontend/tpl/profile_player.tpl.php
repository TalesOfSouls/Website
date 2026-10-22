<?php
use Application\Frontend\Chart;
use Application\Frontend\Provider\AchievementData;
use Application\Frontend\Provider\EncounterData;
use Models\AchievementType;

/** @var \phpOMS\Views\View $this */
$profile = $this->data['profile'];

if ($profile === null) {
    include __DIR__ . '/error/404.tpl.php';
    return;
}

/** @var \Models\Account $profile */
$tab        = $this->data['tab'] ?? 'overview';
$encounters = $this->data['encounters'] ?? [];
$characters = $this->data['characters'] ?? [];
$stats      = $this->data['stats'] ?? [];

$achievements       = $this->data['achievements'] ?? [];
$achievementHistory = AchievementData::pointHistory($achievements);
$achievementPoints  = \array_sum(\array_column($achievements, 'points'));
$recentUnlocks      = \array_slice(\array_reverse($achievementHistory), 0, 5);

$since = $profile->created ?? new \DateTime('2024-07-01');

$listUrl = fn (int $p) : string => '/player/profile/' . $profile->id . '?tab=' . $tab . ($p > 1 ? '&amp;page=' . $p : '');
?>
<div class="floater">
    <div class="profile-banner">
        <p class="profile-back"><a href="/player/profile">Players</a></p>
        <div class="profile-head">
            <div class="avatar"><?= $this->printHtml(\mb_substr($profile->name, 0, 1)); ?></div>
            <div>
                <h1 class="profile-name"><?= $this->printHtml($profile->name); ?></h1>
                <p class="profile-meta">Member since <?= $since->format('Y-m-d'); ?><?= $profile->isDeveloper() ? ' <span class="badge planned">Developer</span>' : ''; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="floater page-section">
    <nav class="tabs">
        <a href="/player/profile/<?= $profile->id; ?>"<?= $tab === 'overview' ? ' class="active"' : ''; ?>>Overview</a>
        <a href="/player/profile/<?= $profile->id; ?>?tab=achievements"<?= $tab === 'achievements' ? ' class="active"' : ''; ?>>Achievements</a>
        <a href="/player/profile/<?= $profile->id; ?>?tab=seasons"<?= $tab === 'seasons' ? ' class="active"' : ''; ?>>Seasons</a>
        <?php foreach (EncounterData::TYPES as $type => $typeTitle) : ?>
            <a href="/player/profile/<?= $profile->id; ?>?tab=<?= $type; ?>"<?= $tab === $type ? ' class="active"' : ''; ?>><?= $typeTitle; ?></a>
        <?php endforeach; ?>
    </nav>

<?php if ($tab === 'overview') : ?>
    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= \number_format($stats['playtime'] ?? 0); ?> h</span><span class="label">Playtime</span></div>
        <div class="stat-tile"><span class="value"><?= \number_format($achievementPoints); ?></span><span class="label">Achievement points</span></div>
        <div class="stat-tile"><span class="value"><?= \number_format($stats['pvp_kills'] ?? 0); ?></span><span class="label">PvP kills</span></div>
        <div class="stat-tile"><span class="value"><?= \number_format($stats['dungeons'] ?? 0); ?></span><span class="label">Dungeons cleared</span></div>
    </div>

    <div class="layout-halves">
        <div class="panel">
            <h2>Characters</h2>
            <table class="content-table">
                <thead><tr><th class="wf-100">Character</th><th>Class</th><th>Level</th><th>Guild</th></tr></thead>
                <tbody>
                <?php foreach ($characters as $characterId => $character) : ?>
                    <tr>
                        <td><a href="/player/character/profile/<?= $characterId; ?>"><?= $this->printHtml($character['name']); ?></a></td>
                        <td><?= $this->printHtml($character['class']); ?></td>
                        <td><?= $character['level']; ?></td>
                        <td><?php if ($character['guild'] > 0) : ?><a href="/guild/profile/<?= $character['guild']; ?>"><?= $this->printHtml($character['guild_name']); ?></a><?php else : ?>-<?php endif; ?></td>
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
                <?php if (empty($recentUnlocks)) : ?>
                    <dt>No achievements yet</dt><dd></dd>
                <?php endif; ?>
            </dl>
            <p><a href="/player/profile/<?= $profile->id; ?>?tab=achievements">All achievements</a></p>
        </div>
    </div>
<?php elseif ($tab === 'achievements') :
    // Achievement points over time (cumulative)
    $points = [['x' => 0, 'y' => 0, 'title' => 'Account created']];
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

    $maxPossible = \array_sum(\array_map(fn ($a) => \array_sum(\array_slice(AchievementType::TIER_POINTS, 0, \count($a['tiers']))), $achievements));
    $completed   = \count(\array_filter($achievements, fn ($a) => $a['reached'] >= \count($a['tiers'])));
    $tiers       = \array_sum(\array_column($achievements, 'reached'));
    $tiersTotal  = \array_sum(\array_map(fn ($a) => \count($a['tiers']), $achievements));

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
        <div class="stat-tile"><span class="value"><?= $tiers; ?> / <?= $tiersTotal; ?></span><span class="label">Tiers reached</span></div>
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
            <label for="<?= $categoryId; ?>"><?= $this->printHtml($category); ?> <small class="muted"><?= $categoryReached; ?>/<?= \array_sum(\array_map(fn ($a) => \count($a['tiers']), $list)); ?></small></label>
        <?php endforeach; ?>

        <?php foreach ($byCategory as $category => $list) : ?>
        <div class="css-tab-panel">
        <div class="achievement-list">
        <?php foreach ($list as $achievement) :
            $tierCount = \count($achievement['tiers']);
            $next      = $achievement['reached'] < $tierCount ? $achievement['tiers'][$achievement['reached']] : null;
            $lower     = $achievement['reached'] === 0 ? 0 : $achievement['tiers'][$achievement['reached'] - 1];
            $tierShare = $next === null ? 100 : \round(($achievement['progress'] - $lower) / \max(1, $next - $lower) * 100);
            $lastDate  = empty($achievement['unlocked']) ? null : \end($achievement['unlocked']);
        ?>
            <div class="achievement<?= $achievement['reached'] === 0 ? ' locked' : ''; ?>">
                <div class="achievement-main">
                    <span class="achievement-name"><?= $this->printHtml($achievement['name']); ?></span>
                    <span class="achievement-desc"><?= $this->printHtml($achievement['description']); ?></span>
                </div>
                <div class="achievement-tiers" title="<?= $achievement['reached']; ?> of <?= $tierCount; ?> tiers reached">
                    <?php for ($t = 1; $t <= $tierCount; ++$t) : ?>
                        <span class="tier<?= $t <= $achievement['reached'] ? ' reached' : ''; ?>"
                            title="Tier <?= AchievementData::romanTier($t); ?>: <?= \number_format($achievement['tiers'][$t - 1]); ?><?= isset($achievement['unlocked'][$t - 1]) ? ' - ' . $achievement['unlocked'][$t - 1]->format('Y-m-d') : ''; ?>"><?= AchievementData::romanTier($t); ?></span>
                    <?php endfor; ?>
                </div>
                <div class="achievement-progress">
                    <span class="share-bar"><span style="width: <?= $tierShare; ?>%"></span></span>
                    <span class="achievement-count">
                        <?= \number_format($achievement['progress']); ?><?= $next !== null ? ' / ' . \number_format($next) : ''; ?>
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

    $seasonDates = fn (array $s) : string => $s['start']->format('M Y') . ' - ' . $s['end']->format('M Y');
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
            <?php if ($selected['current']) : ?>
                <span class="badge progress">Current</span>
            <?php else : ?>
                <span class="badge">Ended</span>
            <?php endif; ?>
        </h2>
        <p class="muted season-dates">
            <?= $selected['start']->format('Y-m-d'); ?> - <?= $selected['end']->format('Y-m-d'); ?>
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
                            <td><a href="/player/character/profile/<?= $character['id']; ?>"><?= $this->printHtml($character['name']); ?></a><?= $character['died'] ? ' <span class="badge closed">Died</span>' : ''; ?></td>
                            <td><?= $this->printHtml($character['class']); ?></td>
                            <td><?= $character['level']; ?><?= $character['level'] >= \Application\Frontend\Provider\SeasonData::MAX_LEVEL ? ' <small class="muted">max</small>' : ''; ?></td>
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
                        <?php if ($season['current']) : ?><span class="badge progress">Current</span><?php endif; ?>
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
    $pvp    = $this->data['pvp'] ?? ['matches' => 0, 'wins' => 0, 'kills' => 0, 'deaths' => 0];
    $rating = $this->data['rating'] ?? null;
?>
    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= $rating ?? '-'; ?></span><span class="label">Rating</span></div>
        <div class="stat-tile"><span class="value"><?= $pvp['wins']; ?> / <?= $pvp['matches'] - $pvp['wins']; ?></span><span class="label">Wins / losses</span></div>
        <div class="stat-tile"><span class="value"><?= $pvp['matches'] === 0 ? '-' : \round($pvp['wins'] / $pvp['matches'] * 100) . '%'; ?></span><span class="label">Win rate</span></div>
        <div class="stat-tile"><span class="value"><?= \number_format($pvp['kills'] / \max(1, $pvp['deaths']), 2); ?></span><span class="label">K/D ratio</span></div>
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
                    <td><?= EncounterData::formatDuration($match['duration']); ?></td>
                    <td><?= $match['score']['own']; ?> : <?= $match['score']['enemy']; ?></td>
                    <td><?= $match['self']['kills']; ?> / <?= $match['self']['deaths']; ?> / <?= $match['self']['assists']; ?></td>
                    <td><?= EncounterData::formatNumber($match['self']['damage']); ?></td>
                    <td><?= EncounterData::formatNumber($match['self']['healing']); ?></td>
                    <td><?= $match['rating_before'] + $match['rating_change']; ?> <span class="<?= $match['rating_change'] >= 0 ? 'rating-up' : 'rating-down'; ?>">(<?= $match['rating_change'] >= 0 ? '+' : ''; ?><?= $match['rating_change']; ?>)</span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($encounters)) : ?>
                <tr><td colspan="10">No matches recorded yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php $page = $this->data['page'] ?? 1; $pages = $this->data['pages'] ?? 1; $pageUrl = $listUrl; include __DIR__ . '/pager.tpl.php'; ?>
    </div>
<?php else : ?>
    <div class="panel">
        <h2><?= EncounterData::TYPES[$tab]; ?></h2>
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
                    <td><?= EncounterData::formatDuration($encounter['duration']); ?></td>
                    <td><?= $tab === 'worldboss' ? $encounter['players'] : $encounter['killed'] . '/' . $encounter['total']; ?></td>
                    <td><?= EncounterData::formatNumber($encounter['self']['damage']); ?></td>
                    <td><?= EncounterData::formatNumber($encounter['self']['healing']); ?></td>
                    <td><?= $encounter['self']['deaths']; ?></td>
                    <td>#<?= $encounter['rank']; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($encounters)) : ?>
                <tr><td colspan="11">Nothing recorded yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php $page = $this->data['page'] ?? 1; $pages = $this->data['pages'] ?? 1; $pageUrl = $listUrl; include __DIR__ . '/pager.tpl.php'; ?>
    </div>
<?php endif; ?>
</div>
