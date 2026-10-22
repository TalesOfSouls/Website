<?php
use Application\Frontend\Chart;
use Application\Frontend\SampleEncounters;

/** @var \phpOMS\Views\View $this */
$profile = $this->data['profile'];
$match   = $this->data['encounter'];
$self    = $match['self'];

$teamNames = ['own' => 'Your team', 'enemy' => 'Enemy team'];

$maxDamage = 1;
foreach ($match['teams'] as $members) {
    foreach ($members as $member) {
        $maxDamage = \max($maxDamage, $member['damage']);
    }
}

$characterLink = function (array $member) : string {
    $name = $this->printHtml($member['name']);

    return $member['id'] > 0
        ? '<a href="/player/character/profile/' . $member['id'] . '">' . $name . '</a>'
        : $name;
};

// Score progression (step lines for both teams)
$series = [];
foreach (['own', 'enemy'] as $side) {
    $points = [];
    foreach ($match['timeline'] as $event) {
        $points[] = [
            'x'     => $event['time'],
            'y'     => $event[$side],
            'title' => SampleEncounters::formatDuration($event['time']) . ' - ' . $teamNames[$side] . ': '
                . $event[$side] . ' ' . $match['unit'],
        ];
    }

    $series[] = [
        'label'       => $teamNames[$side] . ' ' . $match['score'][$side],
        'class'       => $side === 'own' ? 's-own' : 's-enemy',
        'step'        => true,
        'points'      => $points,
        // keep the direct labels apart if both teams end with a similar score
        'labelOffset' => \abs($match['score']['own'] - $match['score']['enemy']) < $match['target'] * 0.12
            ? ($side === 'own' ? -7 : 7)
            : 0,
    ];
}

$tickStep = \max(60, (int) (\ceil($match['duration'] / 6 / 60) * 60));
$xTicks   = \range(0, $match['duration'], $tickStep);
$yTicks   = [0, $match['target'] / 2, $match['target']];

$scoreChart = Chart::line($series, [
    'label'   => 'Score progression',
    'xMax'    => $match['duration'],
    'xEnd'    => $match['duration'],
    'yMax'    => $match['target'],
    'yTicks'  => $yTicks,
    'yFormat' => fn ($v) => (string) \round($v),
    'xTicks'  => $xTicks,
    'xFormat' => fn ($v) => SampleEncounters::formatDuration((int) $v),
]);

$backUrl = '/player/profile/' . $profile->id . '?tab=pvp';
?>
<div class="floater">
    <div class="profile-banner">
        <p class="profile-back"><a href="<?= $backUrl; ?>"><?= $this->printHtml($profile->name); ?> &middot; PvP</a></p>
        <h1 class="profile-name"><?= $this->printHtml($match['title']); ?></h1>
        <p class="profile-meta">
            <?= $this->printHtml($match['location']); ?>
            &middot; <?= $match['date']->format('Y-m-d H:i'); ?>
            &middot; <span class="badge <?= $match['success'] ? 'done' : 'closed'; ?>"><?= $match['success'] ? 'Win' : 'Loss'; ?></span>
        </p>
    </div>
</div>

<div class="floater page-section">
    <div class="notice info">All values are sample data.</div>

    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= $match['score']['own']; ?> : <?= $match['score']['enemy']; ?></span><span class="label">Final score (<?= $this->printHtml($match['unit']); ?>)</span></div>
        <div class="stat-tile"><span class="value"><?= SampleEncounters::formatDuration($match['duration']); ?></span><span class="label">Duration</span></div>
        <div class="stat-tile">
            <span class="value"><?= $match['rating_before'] + $match['rating_change']; ?>
                <small class="<?= $match['rating_change'] >= 0 ? 'rating-up' : 'rating-down'; ?>"><?= $match['rating_change'] >= 0 ? '+' : ''; ?><?= $match['rating_change']; ?></small>
            </span>
            <span class="label">Rating</span>
        </div>
        <div class="stat-tile"><span class="value"><?= $self['kills']; ?> / <?= $self['deaths']; ?> / <?= $self['assists']; ?></span><span class="label"><?= $this->printHtml($self['name']); ?> K / D / A</span></div>
        <div class="stat-tile"><span class="value"><?= SampleEncounters::formatNumber($self['damage']); ?></span><span class="label">Damage</span></div>
        <div class="stat-tile"><span class="value">#<?= $match['rank']; ?></span><span class="label">Damage rank</span></div>
    </div>

    <div class="panel">
        <h2>Score progression</h2>
        <div class="chart-legend">
            <span><i class="legend-swatch s-own"></i>Your team</span>
            <span><i class="legend-swatch s-enemy"></i>Enemy team</span>
        </div>
        <?= $scoreChart; ?>
    </div>

    <?php foreach ($match['teams'] as $side => $members) :
        \usort($members, fn ($a, $b) => $b['damage'] <=> $a['damage']);
        $won = ($side === 'own') === $match['success'];
    ?>
    <div class="panel team-panel team-<?= $side; ?>">
        <h2>
            <?= $teamNames[$side]; ?>
            <span class="badge <?= $won ? 'done' : 'closed'; ?>"><?= $won ? 'Victory' : 'Defeat'; ?></span>
            <span class="team-score"><?= $match['score'][$side]; ?> <?= $this->printHtml($match['unit']); ?></span>
        </h2>
        <?php
            $statsId  = 'stats-' . $side;
            $selfName = $self['name'];
            include __DIR__ . '/encounter_stats.tpl.php';
        ?>
        <?php if ($match['unit'] !== 'kills') : ?>
            <p class="muted team-objectives">
                <?= \ucfirst($this->printHtml($match['unit'])); ?>:
                <?php foreach ($members as $member) : ?>
                    <?= $this->printHtml($member['name']); ?> <?= $member['objectives']; ?><?= $member !== \end($members) ? ' &middot;' : ''; ?>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
