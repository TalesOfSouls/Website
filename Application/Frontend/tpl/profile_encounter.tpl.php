<?php
use Application\Frontend\Chart;
use Application\Frontend\Provider\EncounterData;

/** @var \phpOMS\Views\View $this */
$profile   = $this->data['profile'];
$encounter = $this->data['encounter'];
$type      = $encounter['type'];

// Group sorted by damage
$group = $encounter['group'];
\usort($group, fn ($a, $b) => $b['damage'] <=> $a['damage']);

$maxDamage    = \max(1, $group[0]['damage'] ?? 1);
$totalDamage  = \array_sum(\array_column($group, 'damage'));
$totalHealing = \array_sum(\array_column($group, 'healing'));
$totalDeaths  = \array_sum(\array_column($group, 'deaths'));

$visibleMembers = $type === 'worldboss' ? \array_slice($group, 0, 15) : $group;

$characterLink = function (array $member) : string {
    $name = $this->printHtml($member['name']);

    return $member['id'] > 0
        ? '<a href="/player/character/profile/' . $member['id'] . '">' . $name . '</a>'
        : $name;
};

/**
 * Line chart of the boss health remaining at the end of each attempt
 */
$attemptChart = function (array $attempts) : string {
    $points = [];
    foreach ($attempts as $attempt) {
        $points[] = [
            'x'      => $attempt['no'],
            'y'      => $attempt['hp_left'],
            'title'  => 'Attempt ' . $attempt['no'] . ': '
                . ($attempt['kill'] ? 'Kill' : $attempt['hp_left'] . '% health left')
                . ' (' . EncounterData::formatDuration($attempt['duration']) . ')',
            'marker' => $attempt['kill'] ? 'kill' : null,
        ];
    }

    $count = \count($attempts);

    return Chart::line(
        [['label' => \end($attempts)['kill'] ? 'Kill' : \end($attempts)['hp_left'] . '%', 'class' => 's-hp', 'points' => $points]],
        [
            'label'   => 'Boss health remaining per attempt',
            'xMin'    => $count > 1 ? 1 : 0,
            'xMax'    => \max(2, $count),
            'yMax'    => 100,
            'yTicks'  => [0, 25, 50, 75, 100],
            'yFormat' => fn ($v) => $v . '%',
            'xTicks'  => Chart::countTicks($count),
            'xFormat' => fn ($v) => '#' . $v,
        ]
    );
};

$backUrl = '/player/profile/' . $profile->id . '?tab=' . $type;
?>
<div class="floater">
    <div class="profile-banner">
        <p class="profile-back"><a href="<?= $backUrl; ?>"><?= $this->printHtml($profile->name); ?> &middot; <?= EncounterData::TYPES[$type]; ?></a></p>
        <h1 class="profile-name"><?= $this->printHtml($encounter['title']); ?></h1>
        <p class="profile-meta">
            <?= $this->printHtml($type === 'worldboss' ? $encounter['location'] : $encounter['difficulty']); ?>
            &middot; <?= $encounter['date']->format('Y-m-d H:i'); ?>
            &middot; <span class="badge <?= $encounter['success'] ? 'done' : 'closed'; ?>"><?= $encounter['success'] ? ($type === 'dungeon' ? 'Cleared' : 'Kill') : 'Failed'; ?></span>
        </p>
    </div>
</div>

<div class="floater page-section">

    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= EncounterData::formatDuration($encounter['duration']); ?></span><span class="label">Duration</span></div>
        <?php if ($type === 'worldboss') : ?>
            <div class="stat-tile"><span class="value"><?= \count($group); ?></span><span class="label">Participants</span></div>
        <?php else : ?>
            <div class="stat-tile"><span class="value"><?= $encounter['killed']; ?>/<?= $encounter['total']; ?></span><span class="label">Bosses killed</span></div>
        <?php endif; ?>
        <div class="stat-tile"><span class="value"><?= EncounterData::formatNumber($totalDamage); ?></span><span class="label">Total damage</span></div>
        <div class="stat-tile"><span class="value"><?= EncounterData::formatNumber($totalHealing); ?></span><span class="label">Total healing</span></div>
        <div class="stat-tile"><span class="value"><?= $totalDeaths; ?></span><span class="label">Deaths</span></div>
        <div class="stat-tile"><span class="value">#<?= $encounter['rank']; ?></span><span class="label"><?= $this->printHtml($encounter['self']['name']); ?> rank</span></div>
    </div>

    <div class="panel">
        <h2><?= $type === 'worldboss' ? 'Participants' : 'Group'; ?></h2>
        <?php
            $members  = $visibleMembers;
            $statsId  = 'stats-run';
            $selfName = $encounter['self']['name'];
            include __DIR__ . '/encounter_stats.tpl.php';
        ?>
        <?php if (\count($group) > \count($visibleMembers)) : ?>
            <p class="muted">Top <?= \count($visibleMembers); ?> by damage - and <?= \count($group) - \count($visibleMembers); ?> more participants.</p>
        <?php endif; ?>
    </div>

    <?php foreach ($encounter['bosses'] as $boss) :
        $attempts = $boss['attempts'];
    ?>
    <div class="panel boss-panel">
        <h2>
            <?= $this->printHtml($boss['name']); ?>
            <?php if ($boss['killed']) : ?>
                <span class="badge done">Killed</span>
            <?php elseif (!empty($attempts)) : ?>
                <span class="badge closed">Not killed</span>
            <?php else : ?>
                <span class="badge">Not reached</span>
            <?php endif; ?>
        </h2>

        <?php if (empty($attempts)) : ?>
            <p class="muted">The group did not reach this boss.</p>
        <?php else : ?>
        <div class="boss-grid">
            <div>
                <h3>Health remaining per attempt</h3>
                <?= $attemptChart($attempts); ?>
            </div>
            <div>
                <h3>Attempts (<?= \count($attempts); ?>)</h3>
                <div class="attempt-list">
                <table class="content-table">
                    <thead><tr><th>#</th><th>Duration</th><th class="wf-100">Result</th></tr></thead>
                    <tbody>
                    <?php foreach ($attempts as $attempt) : ?>
                        <tr>
                            <td><?= $attempt['no']; ?></td>
                            <td><?= EncounterData::formatDuration($attempt['duration']); ?></td>
                            <td><?= $attempt['kill'] ? '<span class="badge done">Kill</span>' : 'Wipe at ' . $attempt['hp_left'] . '%'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <?php if (!empty($boss['damage'])) :
            $metricTabs = [
                'damage'   => $boss['killed'] ? 'Damage (kill attempt)' : 'Damage (last attempt)',
                'healing'  => 'Healing done',
                'received' => 'Healing received',
                'taken'    => 'Damage taken',
            ];
            $bossId = 'boss-' . \md5($boss['name']);
        ?>
            <div class="css-tabs">
                <?php $n = 0; foreach ($metricTabs as $metric => $metricTitle) : ++$n; ?>
                    <input type="radio" name="<?= $bossId; ?>" id="<?= $bossId . '-' . $metric; ?>"<?= $n === 1 ? ' checked' : ''; ?>>
                    <label for="<?= $bossId . '-' . $metric; ?>"><?= $metricTitle; ?></label>
                <?php endforeach; ?>

                <?php foreach ($metricTabs as $metric => $metricTitle) :
                    $values = $boss[$metric];
                    \arsort($values);
                    $values = \array_slice($values, 0, 10, true);
                    $max    = \max(1, \reset($values));
                ?>
                <div class="css-tab-panel">
                    <div class="damage-bars">
                        <?php foreach ($values as $memberIndex => $value) :
                            $member = $encounter['group'][$memberIndex];
                        ?>
                            <div class="damage-row<?= $member['name'] === $encounter['self']['name'] ? ' highlight' : ''; ?>" title="<?= $this->printHtml($member['name']); ?>: <?= \number_format($value); ?>">
                                <span class="damage-name"><?= $characterLink($member); ?></span>
                                <span class="share-bar"><span style="width: <?= \round($value / $max * 100, 1); ?>%"></span></span>
                                <span class="share-value"><?= EncounterData::formatNumber($value); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
