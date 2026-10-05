<?php
/** @var \phpOMS\Views\View $this */
$profile = $this->data['profile'];
$samples = $this->data['sample'] ?? ['characters' => [], 'guilds' => []];

if ($profile->id === 0) {
    include __DIR__ . '/error/404.tpl.php';
    return;
}

// @todo Replace the sample values once guild members are stored
$guild = $samples['guilds'][$profile->id] ?? null;

$members = [];
foreach ($guild['members'] ?? [] as $characterId => $role) {
    if (isset($samples['characters'][$characterId])) {
        $members[$characterId] = $samples['characters'][$characterId] + ['role' => $role];
    }
}
?>
<div class="floater">
    <div class="profile-banner">
        <p class="profile-back"><a href="/guild/profile">Guilds</a></p>
        <div class="profile-head">
            <div class="avatar"><?= $this->printHtml(\mb_substr($profile->name, 0, 1)); ?></div>
            <div>
                <h1 class="profile-name"><?= $this->printHtml($profile->name); ?></h1>
                <p class="profile-meta">
                    <?php if ($guild !== null) : ?>
                        [<?= $this->printHtml($guild['tag']); ?>] &middot; <?= $this->printHtml($guild['server']); ?>
                        &middot; <?= $guild['recruiting'] ? 'Recruiting' : 'Not recruiting'; ?>
                        &middot; Focus: <?= $this->printHtml($guild['focus']); ?>
                    <?php else : ?>-<?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="floater page-section">
    <div class="notice info">Guild details are sample values.</div>

    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= $guild !== null ? \count($members) : '-'; ?></span><span class="label">Members</span></div>
        <div class="stat-tile"><span class="value"><?= isset($guild) ? '#' . $guild['raid_rank'] : '-'; ?></span><span class="label">Raid ranking</span></div>
        <div class="stat-tile"><span class="value"><?= isset($guild) ? \substr($guild['founded'], 0, 4) : '-'; ?></span><span class="label">Founded</span></div>
    </div>

    <div class="layout-split right">
        <div class="panel">
            <h2>Members</h2>
            <table class="content-table">
                <thead><tr><th class="wf-100">Character</th><th>Role</th><th>Class</th><th>Level</th></tr></thead>
                <tbody>
                <?php foreach ($members as $characterId => $member) : ?>
                    <tr>
                        <td><a href="/player/character/profile/<?= $characterId; ?>"><?= $this->printHtml($member['name']); ?></a></td>
                        <td><?= $this->printHtml($member['role']); ?></td>
                        <td><?= $this->printHtml($member['class']); ?></td>
                        <td><?= $member['level']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($members)) : ?>
                    <tr><td colspan="4">No members yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="panel">
            <h2>About</h2>
            <p><?= $this->printHtml($guild['description'] ?? 'No guild description yet.'); ?></p>
        </div>
    </div>
</div>
