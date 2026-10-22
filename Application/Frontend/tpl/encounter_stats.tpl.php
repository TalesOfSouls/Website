<?php
/**
 * Character statistics of a dungeon run, raid, world boss fight or PvP team
 *
 * Expects:
 *   $members       array    Members incl. detailed stats (see SampleEncounters::detailedStats)
 *   $statsId       string   Unique id of this block (used for the tabs)
 *   $selfName      string   Name of the character of the profile owner (highlighted)
 *   $characterLink callable Renders the character name/link
 *
 * The tabs work without JavaScript (radio buttons + CSS).
 */
use Application\Frontend\SampleEncounters;

/** @var \phpOMS\Views\View $this */
$tabs = [
    'overview' => 'Overview',
    'damage'   => 'Damage done',
    'healing'  => 'Healing done',
    'received' => 'Healing received',
    'taken'    => 'Damage taken',
    'skills'   => 'Skills',
];

$sortBy = function (array $list, string $key) : array {
    \usort($list, fn ($a, $b) => $b[$key] <=> $a[$key]);

    return $list;
};

$fmt = fn ($v) => SampleEncounters::formatNumber($v);
$pct = fn ($v, $max) => \round($v / \max(1, $max) * 100, 1);
?>
<div class="css-tabs">
    <?php $n = 0; foreach ($tabs as $key => $title) : ++$n; ?>
        <input type="radio" name="<?= $statsId; ?>" id="<?= $statsId . '-' . $key; ?>"<?= $n === 1 ? ' checked' : ''; ?>>
        <label for="<?= $statsId . '-' . $key; ?>"><?= $title; ?></label>
    <?php endforeach; ?>

    <?php /* Overview */ ?>
    <div class="css-tab-panel">
        <table class="content-table">
            <thead>
                <tr>
                    <th>Character</th>
                    <th>Class</th>
                    <th>Role</th>
                    <th>Damage</th>
                    <th>DPS</th>
                    <th>Single target</th>
                    <th>AoE</th>
                    <th>Healing</th>
                    <th>HPS</th>
                    <th>Healing received</th>
                    <th>Damage taken</th>
                    <th>APM</th>
                    <th>Deaths</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sortBy($members, 'damage') as $member) : ?>
                <tr<?= $member['name'] === $selfName ? ' class="highlight"' : ''; ?>>
                    <td><?= $characterLink($member); ?></td>
                    <td><?= $this->printHtml($member['class']); ?></td>
                    <td><?= $member['role']; ?></td>
                    <td><?= $fmt($member['damage']); ?></td>
                    <td><?= \number_format($member['dps']); ?></td>
                    <td><?= $fmt($member['damage_single']); ?></td>
                    <td><?= $fmt($member['damage_aoe']); ?></td>
                    <td><?= $fmt($member['healing']); ?></td>
                    <td><?= \number_format($member['hps']); ?></td>
                    <td><?= $fmt($member['healing_received']); ?></td>
                    <td><?= $fmt($member['damage_taken']); ?></td>
                    <td><?= \number_format($member['apm'], 1); ?></td>
                    <td><?= $member['deaths']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php /* Damage done: single target + AoE */
    $list = $sortBy($members, 'damage');
    $max  = $list[0]['damage'] ?? 1;
    ?>
    <div class="css-tab-panel">
        <div class="chart-legend">
            <span><i class="legend-swatch s-single"></i>Single target</span>
            <span><i class="legend-swatch s-aoe"></i>AoE</span>
        </div>
        <div class="damage-bars">
        <?php foreach ($list as $member) : ?>
            <div class="damage-row stacked<?= $member['name'] === $selfName ? ' highlight' : ''; ?>"
                title="<?= $this->printHtml($member['name']); ?>: <?= \number_format($member['damage_single']); ?> single target, <?= \number_format($member['damage_aoe']); ?> AoE">
                <span class="damage-name"><?= $characterLink($member); ?></span>
                <span class="stack-bar">
                    <span class="s-single" style="width: <?= $pct($member['damage_single'], $max); ?>%"></span><span class="s-aoe" style="width: <?= $pct($member['damage_aoe'], $max); ?>%"></span>
                </span>
                <span class="share-value"><?= $fmt($member['damage']); ?></span>
                <span class="split-value"><?= $fmt($member['damage_single']); ?> / <?= $fmt($member['damage_aoe']); ?></span>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <?php /* Healing done: single target + AoE */
    $list = $sortBy($members, 'healing');
    $max  = $list[0]['healing'] ?? 1;
    ?>
    <div class="css-tab-panel">
        <div class="chart-legend">
            <span><i class="legend-swatch s-single"></i>Single target</span>
            <span><i class="legend-swatch s-aoe"></i>AoE</span>
        </div>
        <div class="damage-bars">
        <?php foreach ($list as $member) : ?>
            <div class="damage-row stacked<?= $member['name'] === $selfName ? ' highlight' : ''; ?>"
                title="<?= $this->printHtml($member['name']); ?>: <?= \number_format($member['healing_single']); ?> single target, <?= \number_format($member['healing_aoe']); ?> AoE">
                <span class="damage-name"><?= $characterLink($member); ?></span>
                <span class="stack-bar">
                    <span class="s-single" style="width: <?= $pct($member['healing_single'], $max); ?>%"></span><span class="s-aoe" style="width: <?= $pct($member['healing_aoe'], $max); ?>%"></span>
                </span>
                <span class="share-value"><?= $fmt($member['healing']); ?></span>
                <span class="split-value"><?= $fmt($member['healing_single']); ?> / <?= $fmt($member['healing_aoe']); ?></span>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <?php /* Healing received & damage taken */
    foreach (['healing_received' => 'healing received', 'damage_taken' => 'damage taken'] as $metric => $metricName) :
        $list = $sortBy($members, $metric);
        $max  = $list[0][$metric] ?? 1;
    ?>
    <div class="css-tab-panel">
        <div class="damage-bars">
        <?php foreach ($list as $member) : ?>
            <div class="damage-row<?= $member['name'] === $selfName ? ' highlight' : ''; ?>" title="<?= $this->printHtml($member['name']); ?>: <?= \number_format($member[$metric]); ?> <?= $metricName; ?>">
                <span class="damage-name"><?= $characterLink($member); ?></span>
                <span class="share-bar"><span style="width: <?= $pct($member[$metric], $max); ?>%"></span></span>
                <span class="share-value"><?= $fmt($member[$metric]); ?></span>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php /* Skills */ ?>
    <div class="css-tab-panel">
        <p class="muted skill-help">
            <strong>APM</strong>: actions per minute &middot;
            <strong>Cooldown usage</strong>: how many of the possible casts of skills with a cooldown were used &middot;
            <strong>Active</strong>: share of the fight the character was casting
        </p>
        <div class="skill-summary skill-head">
            <span>Character</span><span>APM</span><span>Casts</span><span>Cooldown usage</span><span>Active</span>
        </div>
        <?php foreach ($sortBy($members, 'damage') as $member) : ?>
        <details class="skill-member<?= $member['name'] === $selfName ? ' highlight' : ''; ?>">
            <summary class="skill-summary">
                <span class="damage-name"><?= $this->printHtml($member['name']); ?> <small class="muted"><?= $this->printHtml($member['class']); ?></small></span>
                <span><?= \number_format($member['apm'], 1); ?></span>
                <span><?= \number_format($member['casts']); ?></span>
                <span class="usage">
                    <span class="share-bar"><span class="<?= $member['cooldown_usage'] < 75 ? 'low' : ''; ?>" style="width: <?= $member['cooldown_usage']; ?>%"></span></span>
                    <span><?= $member['cooldown_usage']; ?>%</span>
                </span>
                <span><?= $member['active']; ?>%</span>
            </summary>
            <table class="content-table skill-table">
                <thead>
                    <tr>
                        <th>Skill</th>
                        <th>Type</th>
                        <th>Cooldown</th>
                        <th>Casts</th>
                        <th>Possible</th>
                        <th class="wf-100">Usage</th>
                        <th>Amount</th>
                        <th>Per cast</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($member['skills'] as $skill) :
                    $usage = $skill['possible'] > 0 ? (int) \round($skill['casts'] / $skill['possible'] * 100) : 0;
                ?>
                    <tr>
                        <td><?= $this->printHtml($skill['name']); ?></td>
                        <td><?= \ucfirst($skill['kind']); ?><?= $skill['kind'] !== 'utility' ? ' &middot; ' . ($skill['target'] === 'aoe' ? 'AoE' : 'Single') : ''; ?></td>
                        <td><?= $skill['cd'] > 0 ? $skill['cd'] . 's' : 'Filler'; ?></td>
                        <td><?= $skill['casts']; ?></td>
                        <td><?= $skill['possible']; ?></td>
                        <td>
                            <div class="share" title="<?= $skill['casts']; ?> of <?= $skill['possible']; ?> possible casts">
                                <span class="share-bar"><span class="<?= $usage < 75 ? 'low' : ''; ?>" style="width: <?= $usage; ?>%"></span></span>
                                <span class="share-value"><?= $usage; ?>%</span>
                            </div>
                        </td>
                        <td><?= $skill['amount'] > 0 ? $fmt($skill['amount']) : '-'; ?></td>
                        <td><?= $skill['amount'] > 0 && $skill['casts'] > 0 ? $fmt($skill['amount'] / $skill['casts']) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </details>
        <?php endforeach; ?>
    </div>
</div>
