<?php
use Models\EquipmentSlotMapper;
use Models\PlayerClassMapper;

/** @var \phpOMS\Views\View $this */
$classes = PlayerClassMapper::getAll()->executeGetArray();
$slots   = EquipmentSlotMapper::getAll()->executeGetArray();

$time = time();

$selectedClass = $this->request->getDataString('class') ?? '';
$level         = \max(1, \min(99, $this->request->getDataInt('level') ?? 99));

$primaryStats = [
    'Strength' => 120, 'Agility' => 85, 'Intelligence' => 40,
    'Dexterity' => 60, 'Accuracy' => 55, 'Stamina' => 140,
];

$secondaryStats = [
    'Health' => '12,450', 'Resource' => '1,200', 'Physical damage' => '845 - 1,020',
    'Critical chance' => '18%', 'Critical damage' => '175%', 'Physical defense' => '2,310',
    'Elemental defense' => '1,140', 'Magic defense' => '980', 'Movement speed' => '100%',
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Build Planner</h1>
    </div>
</div>

<div id="character" class="page-section">
    <div class="floater">
        <form class="filter-bar" method="GET" action="/build/planner">
            <select name="class">
                <option value="" disabled<?= $selectedClass === '' ? ' selected' : ''; ?>>Class</option>
                <?php foreach ($classes as $category) : ?>
                <?php if ($category->parent === null
                    && ($category->releaseDate === null || $category->releaseDate->getTimestamp() < $time)) : ?>
                    <optgroup label="<?= $this->printHtml($category->name); ?>">
                    <?php foreach ($classes as $cl) : ?>
                        <?php if ($cl->parent === $category->id
                            && ($cl->releaseDate === null || $cl->releaseDate->getTimestamp() < $time)) : ?>
                            <option<?= $selectedClass === $cl->name ? ' selected' : ''; ?>><?= $this->printHtml($cl->name); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <input type="number" name="level" min="1" max="99" value="<?= $level; ?>" title="Level">
            <select name="mode">
                <option>Open World</option>
                <option>Dungeons</option>
                <option>Raids</option>
                <option>PvP Arena</option>
                <option>PvP WvW</option>
            </select>
            <input type="text" name="name" placeholder="Build name">
            <button type="submit">Apply</button>
            <button type="submit" class="featured" formmethod="POST">Save build</button>
        </form>

        <div class="notice info">The planner is a preview - stats are sample values and builds can't be saved yet.</div>

        <div class="layout-split right">
            <div>
                <div class="panel">
                    <h2>Equipment</h2>
                    <?php
                        // @todo Items can be chosen once builds can be saved
                        $equipment   = [];
                        $emptyText   = 'Choose item';
                        $viewCaption = ($selectedClass === '' ? 'No class selected' : $this->printHtml($selectedClass)) . ' &middot; Level ' . $level;
                        include __DIR__ . '/equipment_paperdoll.tpl.php';
                    ?>
                </div>
                <div class="panel">
                    <h2>Skill tree <span class="muted">(<?= $selectedClass === '' ? 'no class selected' : $this->printHtml($selectedClass); ?>)</span></h2>
                    <div class="skill-tree">
                        <?php for ($i = 1; $i <= 25; ++$i) : ?>
                            <div class="skill-node<?= \in_array($i, [1, 2, 6, 7, 12, 13, 18]) ? ' active' : ''; ?>">Skill <?= $i; ?></div>
                        <?php endfor; ?>
                    </div>
                    <p class="muted">7 / 40 skill points spent</p>
                </div>
            </div>
            <div>
                <div class="panel">
                    <h2>Attributes</h2>
                    <dl class="stat-list">
                        <dt>Level</dt><dd><?= $level; ?></dd>
                        <?php foreach ($primaryStats as $name => $value) : ?>
                            <dt><?= $name; ?></dt><dd><?= $value; ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </div>
                <div class="panel">
                    <h2>Stats</h2>
                    <dl class="stat-list">
                        <?php foreach ($secondaryStats as $name => $value) : ?>
                            <dt><?= $name; ?></dt><dd><?= $value; ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
