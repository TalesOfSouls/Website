<?php
use Application\Frontend\Chart;
use Application\Frontend\Provider\CharacterData;
use Application\Frontend\Provider\SeasonData;
use Models\EquipmentSlotMapper;

/** @var \phpOMS\Views\View $this */
$profile = $this->data['profile'];

if ($profile === null) {
    include __DIR__ . '/error/404.tpl.php';
    return;
}

/** @var \Models\Character $profile */
$character   = $this->data['character'];
$equipment   = $this->data['equipment'] ?? [];
$professions = $this->data['professions'] ?? [];
$progression = $this->data['progression'] ?? [];
$rank        = $this->data['rank'] ?? null;

/** @var null|\Models\CharacterLog $statistics */
$statistics = $this->data['statistics'] ?? null;

$slots = EquipmentSlotMapper::getAll()->executeGetArray();

$stats = [
    'Race'     => $this->printHtml($this->data['race'] ?? '-'),
    'Created'  => $character['created'] !== null ? $character['created']->format('Y-m-d') : '-',
];

if ($statistics !== null) {
    $stats += [
        'Gold'           => \number_format($statistics->gold),
        'Monsters slain' => \number_format($statistics->mobsKilled),
        'Players slain'  => \number_format($statistics->playersKilled),
        'Dungeons'       => \number_format($statistics->dungeonsSuccess) . ' / ' . \number_format($statistics->dungeons),
        'Raids'          => \number_format($statistics->raidsSuccess) . ' / ' . \number_format($statistics->raids),
        'PvP matches'    => \number_format($statistics->pvp),
    ];
}

$tab = $this->data['tab'] ?? 'overview';

// Level progression chart
$levelChart = '';
if (\count($progression) > 1) {
    $points = [];
    foreach ($progression as $step) {
        $points[] = [
            'x'     => $step['hours'],
            'y'     => $step['level'],
            'title' => 'Level ' . $step['level'] . ' - ' . $step['date']->format('Y-m-d') . ' after ' . \number_format($step['hours'], 1) . ' h',
        ];
    }

    $maxHours = \max(1, \end($progression)['hours']);
    $tickStep = \max(1, (int) (\ceil($maxHours / 6 / 5) * 5));
    $maxLevel = $character['season'] !== null ? SeasonData::MAX_LEVEL : 99;

    $levelChart = Chart::line(
        [['label' => 'Level ' . $character['level'], 'class' => 's-hp', 'points' => $points]],
        [
            'label'   => 'Level progression',
            'xMax'    => $maxHours,
            'yMax'    => $maxLevel + 1,
            'yTicks'  => $maxLevel === 99 ? [1, 25, 50, 75, 99] : [1, 20, 40, 60],
            'xTicks'  => \range(0, (int) $maxHours, $tickStep),
            'xFormat' => fn ($v) => $v . ' h',
        ]
    );
}

$eventUrl = function (array $params) use ($profile) : string {
    $params = \array_filter($params + ['tab' => 'log']);

    return '/player/character/profile/' . $profile->id . '?' . \http_build_query($params);
};
?>
<div class="floater">
    <div class="profile-banner">
        <p class="profile-back"><a href="/player/character/profile">Characters</a></p>
        <div class="profile-head">
            <div class="avatar"><?= $this->printHtml(\mb_substr($profile->name, 0, 1)); ?></div>
            <div>
                <h1 class="profile-name"><?= $this->printHtml($profile->name); ?></h1>
                <p class="profile-meta">
                    Level <?= $character['level']; ?> <?= $this->printHtml($character['class']); ?> &middot; <?= $this->printHtml($character['server']); ?>
                    <?php if ($character['guild'] > 0) : ?>
                        &middot; <a class="text-link" href="/guild/profile/<?= $character['guild']; ?>">&lt;<?= $this->printHtml($character['guild_name']); ?>&gt;</a>
                    <?php endif; ?>
                    &middot; Player: <a class="text-link" href="/player/profile/<?= $character['player']; ?>"><?= $this->printHtml($character['player_name']); ?></a>
                    <?php if ($character['season'] !== null) : ?> <span class="badge planned">Seasonal</span><?php endif; ?>
                    <?php if ($character['hardcore']) : ?> <span class="badge progress">Hardcore</span><?php endif; ?>
                    <?php if ($character['dead']) : ?> <span class="badge closed">Dead</span><?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="floater page-section">
    <nav class="tabs">
        <a href="/player/character/profile/<?= $profile->id; ?>"<?= $tab === 'overview' ? ' class="active"' : ''; ?>>Overview</a>
        <a href="/player/character/profile/<?= $profile->id; ?>?tab=log"<?= $tab === 'log' ? ' class="active"' : ''; ?>>Event log</a>
    </nav>

<?php if ($tab === 'overview') : ?>

    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= $character['level']; ?></span><span class="label">Level</span></div>
        <div class="stat-tile"><span class="value"><?= \number_format($character['playtime']); ?> h</span><span class="label">Playtime</span></div>
        <div class="stat-tile"><span class="value"><?= $rank !== null ? '#' . \number_format($rank) : '-'; ?></span><span class="label">Ladder rank</span></div>
        <div class="stat-tile"><span class="value"><?= \number_format($character['item_level']); ?></span><span class="label">Item level</span></div>
    </div>

    <div class="layout-split right">
        <div class="panel">
            <h2>Equipment</h2>
            <?php
                $viewCaption = $this->printHtml($profile->name)
                    . ' &middot; Level ' . $character['level'] . ' ' . $this->printHtml($character['class']);
                include __DIR__ . '/equipment_paperdoll.tpl.php';
            ?>
        </div>
        <div class="panel">
            <h2>Details</h2>
            <dl class="stat-list">
                <?php foreach ($stats as $name => $value) : ?>
                    <dt><?= $name; ?></dt><dd><?= $value; ?></dd>
                <?php endforeach; ?>
            </dl>
            <h3>Proficiencies</h3>
            <dl class="stat-list">
                <?php foreach ($professions as $name => $professionLevel) : ?>
                    <dt><?= $this->printHtml($name); ?></dt><dd><?= $professionLevel; ?></dd>
                <?php endforeach; ?>
                <?php if (empty($professions)) : ?>
                    <dt>-</dt><dd></dd>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <?php if ($levelChart !== '') : ?>
    <div class="panel">
        <h2>Level progression</h2>
        <?= $levelChart; ?>
        <p class="muted chart-note">Level over the playtime of the character. Hover a point for the date.</p>
    </div>
    <?php endif; ?>
<?php else :
    $events    = $this->data['events'] ?? [];
    $eventType = $this->data['eventType'] ?? '';
    $page      = $this->data['page'] ?? 1;
    $pages     = $this->data['pages'] ?? 1;
?>
    <div class="panel">
        <h2>Event log <span class="muted">(<?= \number_format($this->data['eventCount'] ?? 0); ?>)</span></h2>

        <div class="filter-chips">
            <a href="<?= $eventUrl([]); ?>"<?= $eventType === '' ? ' class="active"' : ''; ?>>All</a>
            <?php foreach (CharacterData::EVENT_TYPES as $key => $typeTitle) : ?>
                <a href="<?= $eventUrl(['type' => $key]); ?>"<?= $eventType === $key ? ' class="active"' : ''; ?>><?= $typeTitle; ?></a>
            <?php endforeach; ?>
        </div>

        <ol class="event-log">
        <?php foreach ($events as $event) : ?>
            <li class="event event-<?= $event['type']; ?>">
                <span class="event-date"><?= $event['date']->format('Y-m-d H:i'); ?></span>
                <span class="event-type"><?= CharacterData::EVENT_TYPES[$event['type']]; ?></span>
                <span class="event-title<?= $event['rarity'] !== '' ? ' rarity-' . $event['rarity'] : ''; ?>">
                    <?php if ($event['link'] !== '') : ?>
                        <a href="<?= $event['link']; ?>"><?= $this->printHtml($event['title']); ?></a>
                    <?php else : ?>
                        <?= $this->printHtml($event['title']); ?>
                    <?php endif; ?>
                </span>
                <span class="event-detail"><?= $this->printHtml($event['detail']); ?></span>
            </li>
        <?php endforeach; ?>
        </ol>

        <?php if (empty($events)) : ?>
            <p class="muted">No events recorded.</p>
        <?php endif; ?>

        <?php
            $pageUrl = fn (int $p) : string => $eventUrl(['type' => $eventType, 'page' => $p]);
            include __DIR__ . '/pager.tpl.php';
        ?>
    </div>
<?php endif; ?>
</div>
