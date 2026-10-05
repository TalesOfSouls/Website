<?php
use Models\EquipmentSlotMapper;

/** @var \phpOMS\Views\View $this */
$profile = $this->data['profile'];
$samples = $this->data['sample'] ?? ['players' => [], 'characters' => [], 'guilds' => []];

if ($profile->id === 0) {
    include __DIR__ . '/error/404.tpl.php';
    return;
}

$slots = EquipmentSlotMapper::getAll()->executeGetArray();

// @todo Replace the sample values once the character table is filled by the game servers
$character = $samples['characters'][$profile->id] ?? null;
$player    = $samples['players'][$character['player'] ?? 0] ?? null;
$guild     = $samples['guilds'][$character['guild'] ?? 0] ?? null;

$stats = [
    'Health' => '12,450', 'Resource' => '1,200', 'Strength' => 120, 'Agility' => 85,
    'Intelligence' => 40, 'Dexterity' => 60, 'Accuracy' => 55, 'Stamina' => 140,
];
?>
<div class="floater">
    <div class="profile-banner">
        <p class="profile-back"><a href="/player/character/profile">Characters</a></p>
        <div class="profile-head">
            <div class="avatar"><?= $this->printHtml(\mb_substr($profile->name, 0, 1)); ?></div>
            <div>
                <h1 class="profile-name"><?= $this->printHtml($profile->name); ?></h1>
                <p class="profile-meta">
                    <?= $character !== null ? 'Level ' . $character['level'] . ' ' . $this->printHtml($character['class']) . ' &middot; ' . $this->printHtml($character['server']) : '-'; ?>
                    <?php if ($guild !== null) : ?>
                        &middot; <a class="text-link" href="/guild/profile/<?= $character['guild']; ?>">&lt;<?= $this->printHtml($guild['name']); ?>&gt;</a>
                    <?php endif; ?>
                    <?php if ($player !== null) : ?>
                        &middot; Player: <a class="text-link" href="/player/profile/<?= $character['player']; ?>"><?= $this->printHtml($player['name']); ?></a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="floater page-section">
    <div class="notice info">Character details are sample values.</div>

    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= $character['level'] ?? '-'; ?></span><span class="label">Level</span></div>
        <div class="stat-tile"><span class="value"><?= isset($character) ? $character['playtime'] . ' h' : '-'; ?></span><span class="label">Playtime</span></div>
        <div class="stat-tile"><span class="value"><?= isset($character) ? '#' . $character['rank'] : '-'; ?></span><span class="label">Ladder rank</span></div>
        <div class="stat-tile"><span class="value"><?= isset($character) ? \number_format($character['item_level']) : '-'; ?></span><span class="label">Item level</span></div>
    </div>

    <div class="layout-split right">
        <div class="panel">
            <h2>Equipment</h2>
            <div class="equipment-grid">
                <?php foreach ($slots as $slot) : ?>
                <div class="equipment-slot">
                    <span class="slot-name"><?= $this->printHtml($slot->name); ?></span>
                    <span class="muted">-</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="panel">
            <h2>Stats</h2>
            <dl class="stat-list">
                <?php foreach ($stats as $name => $value) : ?>
                    <dt><?= $name; ?></dt><dd><?= $value; ?></dd>
                <?php endforeach; ?>
            </dl>
            <h3>Proficiencies</h3>
            <dl class="stat-list">
                <dt>Forging</dt><dd>320</dd>
                <dt>Mining</dt><dd>300</dd>
                <dt>Cooking</dt><dd>145</dd>
            </dl>
        </div>
    </div>
</div>
