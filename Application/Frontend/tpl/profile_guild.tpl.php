<?php
/** @var \phpOMS\Views\View $this */
$profile = $this->data['profile'];

if ($profile === null) {
    include __DIR__ . '/error/404.tpl.php';
    return;
}

/** @var \Models\Guild $profile */
$members = \array_values($profile->members);
\usort($members, function ($a, $b) {
    // founder, officers, ... then by level
    return ($a->rank->id <=> $b->rank->id) ?: ($b->character->getLevel() <=> $a->character->getLevel());
});

$raidRank = $this->data['raidRank'] ?? null;
$focus    = $profile->getFocus();
?>
<div class="floater">
    <div class="profile-banner">
        <p class="profile-back"><a href="/guild/profile">Guilds</a></p>
        <div class="profile-head">
            <div class="avatar"><?= $this->printHtml(\mb_substr($profile->name, 0, 1)); ?></div>
            <div>
                <h1 class="profile-name"><?= $this->printHtml($profile->name); ?></h1>
                <p class="profile-meta">
                    [<?= $this->printHtml($profile->tag); ?>] &middot; <?= $this->printHtml($profile->server->name); ?>
                    &middot; <?= $profile->recruiting ? 'Recruiting' : 'Not recruiting'; ?>
                    <?php if (!empty($focus)) : ?>&middot; Focus: <?= $this->printHtml(\implode(', ', $focus)); ?><?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="floater page-section">
    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= \count($members); ?></span><span class="label">Members</span></div>
        <div class="stat-tile"><span class="value"><?= $raidRank !== null ? '#' . $raidRank : '-'; ?></span><span class="label">Raid ranking</span></div>
        <div class="stat-tile"><span class="value"><?= $profile->created?->format('Y') ?? '-'; ?></span><span class="label">Founded</span></div>
    </div>

    <div class="layout-split right">
        <div class="panel">
            <h2>Members</h2>
            <table class="content-table">
                <thead><tr><th class="wf-100">Character</th><th>Rank</th><th>Class</th><th>Level</th><th>Joined</th></tr></thead>
                <tbody>
                <?php foreach ($members as $member) : ?>
                    <tr>
                        <td><a href="/player/character/profile/<?= $member->character->id; ?>"><?= $this->printHtml($member->character->name); ?></a></td>
                        <td class="nowrap"><?= $this->printHtml($member->rank->name); ?></td>
                        <td><?= $this->printHtml($member->character->class->name); ?></td>
                        <td><?= $member->character->getLevel(); ?></td>
                        <td class="nowrap"><?= $member->joined?->format('Y-m-d') ?? '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($members)) : ?>
                    <tr><td colspan="5">No members yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="panel">
            <h2>About</h2>
            <p><?= $this->printHtml($profile->description !== '' ? $profile->description : 'No guild description yet.'); ?></p>
            <dl class="stat-list">
                <?php foreach ($profile->ranks as $rank) :
                    $count = \count(\array_filter($members, fn ($m) => $m->rank->id === $rank->id));
                    if ($count === 0) { continue; }
                ?>
                    <dt><?= $this->printHtml($rank->name); ?></dt><dd><?= $count; ?></dd>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>
</div>
