<?php
use Models\PlayerClassMapper;

$classes = PlayerClassMapper::getAll()->executeGetArray();

$time = time();
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Builds</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/build/list">
        <select name="class">
            <option disabled selected>Class
            <option>Any
            <?php foreach ($classes as $category) : ?>
            <?php if ($category->parent === null
                && ($category->releaseDate === null || $category->releaseDate->getTimestamp() < $time)) : ?>
                <optgroup label="<?= $this->printHtml($category->name); ?>">
                <?php foreach ($classes as $cl) : ?>
                    <?php if ($cl->parent === $category->id
                        && ($cl->releaseDate === null || $cl->releaseDate->getTimestamp() < $time)) : ?>
                        <option><?= $this->printHtml($cl->name); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
                </optgroup>
            <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <select name="mode">
            <option disabled selected>Mode
            <option>Any
            <optgroup label="PvP">
                <option>PvP Arena
                <option>PvP WvW
            </optgroup>
            <option>Open World
            <option>Dungeons
            <option>Raids
        </select>

        <input type="text" name="name" placeholder="Name">
        <button type="submit">Filter</button>
        <a class="button featured" href="/build/planner">Create build</a>
    </form>

    <div class="floater">
        <table class="content-table">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Mode</th>
                    <th class="wf-100">Name</th>
                    <th>Creator</th>
                    <th>Updated</th>
                    <th>Followers</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // @todo Sample builds until builds can be saved
                $builds = [
                    ['Paladin', 'Raids', 'Radiant Bulwark - main tank', 'Aldric', '2026-09-28', 1240],
                    ['Mage', 'Dungeons', 'Glass Cannon Frost Mage', 'Mirelle', '2026-09-25', 980],
                    ['Berserker', 'PvP Arena', 'Bleed Berserker 2v2', 'Thorgar', '2026-09-21', 760],
                    ['Druid', 'Open World', 'Shapeshift leveling guide', 'Sylvara', '2026-09-18', 655],
                    ['Necromancer', 'Raids', 'Minion army DPS', 'Corvin', '2026-09-15', 540],
                    ['Priest', 'Dungeons', 'Holy healer - mythic ready', 'Elowen', '2026-09-12', 512],
                    ['Assassin', 'PvP WvW', 'Roaming poison assassin', 'Draven', '2026-09-10', 433],
                    ['Hunter', 'Open World', 'Pet tank solo farming', 'Fenrik', '2026-09-02', 310],
                    ['Knight', 'PvP WvW', 'Frontline zerg knight', 'Torvald', '2026-08-29', 287],
                    ['Shaman', 'Raids', 'Totem support shaman', 'Nimue', '2026-08-21', 198],
                ];
                foreach ($builds as $build) : ?>
                <tr>
                    <td><?= $build[0]; ?></td>
                    <td><?= $build[1]; ?></td>
                    <td><a href="/build/planner"><?= $build[2]; ?></a></td>
                    <td><?= $build[3]; ?></td>
                    <td><?= $build[4]; ?></td>
                    <td><?= \number_format($build[5]); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>