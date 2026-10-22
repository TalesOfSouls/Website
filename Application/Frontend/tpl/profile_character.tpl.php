<?php
use Application\Frontend\Chart;
use Application\Frontend\SampleCharacterHistory;
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

// @todo Sample equipment until the character equipment is stored
$sampleItems = [
    'Head'            => ['Runed Helmet', 'Hood of the Pale Abbot', 'Iron Coif'],
    'Neck'            => ['Ember Pendant', 'Moonstone Amulet', 'Chain of the Drowned'],
    'Shoulder'        => ['Ashen Pauldrons', 'Mantle of Whispers', 'Steel Spaulders'],
    'Back'            => ['Cloak of the Styx', 'Wolfhide Cape', 'Traveler\'s Cloak'],
    'Body'            => ['Dragonscale Chestplate', 'Robe of Embers', 'Leather Jerkin'],
    'Arms'            => ['Forged Vambraces', 'Silk Wraps', 'Bracers of Thorns'],
    'Belt'            => ['Girdle of the Tyrant', 'Rope Belt', 'Studded Belt'],
    'Belt Attachment' => ['Healing Flask', 'Pouch of Runes', 'Throwing Knives'],
    'Pants'           => ['Plated Greaves', 'Moonweave Trousers', 'Linen Pants'],
    'Boots'           => ['Dragonscale Boots', 'Boots of Haste', 'Worn Boots'],
    'Ring'            => ['Band of Ashes', 'Signet of the Choir', 'Copper Ring'],
    'Hand 1'          => ['Steel Longsword', 'Staff of the Moon', 'Runed Axe'],
    'Hand 2'          => ['Tower Shield', 'Spellbook of Frost', 'Parrying Dagger'],
    'Artifact'        => ['Shard of the Styx', 'Heart of Embers', 'Old Compass'],
];

$rarities  = ['orange', 'yellow', 'blue', 'white'];
$equipment = [];
if ($character !== null) {
    foreach ($slots as $index => $slot) {
        $pool = $sampleItems[$slot->name] ?? [];
        if (empty($pool) || ($character['level'] < 30 && $index % 3 === 2)) {
            continue; // low level characters have free slots
        }

        $seed  = $profile->id + $index;
        $level = \max(1, $character['level'] - $seed % 4);

        $equipment[$slot->id] = [
            'name'   => $pool[$seed % \count($pool)],
            'rarity' => $rarities[\min(3, \intdiv(99 - $character['level'], 20) + $seed % 2)],
            'level'  => $level,
        ];
    }
}

$stats = [
    'Health' => '12,450', 'Resource' => '1,200', 'Strength' => 120, 'Agility' => 85,
    'Intelligence' => 40, 'Dexterity' => 60, 'Accuracy' => 55, 'Stamina' => 140,
];

$tab = $this->data['tab'] ?? 'overview';

// Level progression chart
$levelChart  = '';
$professions = [];
if ($character !== null) {
    $created     = $player['since'] ?? '2024-07-01';
    $progression = SampleCharacterHistory::levelProgression($profile->id, $character['level'], $character['playtime'], $created);
    $professions = SampleCharacterHistory::professions($profile->id, $character['level']);

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

    $levelChart = Chart::line(
        [['label' => 'Level ' . $character['level'], 'class' => 's-hp', 'points' => $points]],
        [
            'label'   => 'Level progression',
            'xMax'    => $maxHours,
            'yMax'    => 100,
            'yTicks'  => [1, 25, 50, 75, 99],
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
    <nav class="tabs">
        <a href="/player/character/profile/<?= $profile->id; ?>"<?= $tab === 'overview' ? ' class="active"' : ''; ?>>Overview</a>
        <a href="/player/character/profile/<?= $profile->id; ?>?tab=log"<?= $tab === 'log' ? ' class="active"' : ''; ?>>Event log</a>
    </nav>

    <div class="notice info">Character details are sample values.</div>
<?php if ($tab === 'overview') : ?>

    <div class="stat-tiles">
        <div class="stat-tile"><span class="value"><?= $character['level'] ?? '-'; ?></span><span class="label">Level</span></div>
        <div class="stat-tile"><span class="value"><?= isset($character) ? $character['playtime'] . ' h' : '-'; ?></span><span class="label">Playtime</span></div>
        <div class="stat-tile"><span class="value"><?= isset($character) ? '#' . $character['rank'] : '-'; ?></span><span class="label">Ladder rank</span></div>
        <div class="stat-tile"><span class="value"><?= isset($character) ? \number_format($character['item_level']) : '-'; ?></span><span class="label">Item level</span></div>
    </div>

    <div class="layout-split right">
        <div class="panel">
            <h2>Equipment</h2>
            <?php
                $viewCaption = $this->printHtml($profile->name)
                    . ($character !== null ? ' &middot; Level ' . $character['level'] . ' ' . $this->printHtml($character['class']) : '');
                include __DIR__ . '/equipment_paperdoll.tpl.php';
            ?>
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
            <?php foreach (SampleCharacterHistory::EVENT_TYPES as $key => $typeTitle) : ?>
                <a href="<?= $eventUrl(['type' => $key]); ?>"<?= $eventType === $key ? ' class="active"' : ''; ?>><?= $typeTitle; ?></a>
            <?php endforeach; ?>
        </div>

        <ol class="event-log">
        <?php foreach ($events as $event) : ?>
            <li class="event event-<?= $event['type']; ?>">
                <span class="event-date"><?= $event['date']->format('Y-m-d H:i'); ?></span>
                <span class="event-type"><?= SampleCharacterHistory::EVENT_TYPES[$event['type']]; ?></span>
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

        <?php if ($pages > 1) : ?>
        <nav class="pager">
            <?php if ($page > 1) : ?><a href="<?= $eventUrl(['type' => $eventType, 'page' => $page - 1]); ?>">&laquo;</a><?php endif; ?>
            <?php for ($p = \max(1, $page - 3); $p <= \min($pages, $page + 3); ++$p) : ?>
                <?php if ($p === $page) : ?>
                    <span class="current"><?= $p; ?></span>
                <?php else : ?>
                    <a href="<?= $eventUrl(['type' => $eventType, 'page' => $p]); ?>"><?= $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $pages) : ?><a href="<?= $eventUrl(['type' => $eventType, 'page' => $page + 1]); ?>">&raquo;</a><?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>
