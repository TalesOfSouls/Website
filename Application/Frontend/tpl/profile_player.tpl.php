<?php
/** @var \phpOMS\Views\View $this */
$profile = $this->data['profile'];
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

$achievements = [
    ['No lifer', '2026-09-30'],
    ['Delver', '2026-09-12'],
    ['Gold Digger', '2026-08-28'],
    ['Loot Goblin', '2026-08-02'],
];
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
    <div class="notice info">Profile statistics are sample values.</div>

    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= isset($player) ? $player['playtime'] . ' h' : '-'; ?></span><span class="label">Playtime</span></div>
        <div class="stat-tile"><span class="value"><?= isset($player) ? \number_format($player['achievement_points']) : '-'; ?></span><span class="label">Achievement points</span></div>
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
                <?php foreach ($achievements as $achievement) : ?>
                    <dt><?= $achievement[0]; ?></dt><dd><?= $achievement[1]; ?></dd>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>
</div>
