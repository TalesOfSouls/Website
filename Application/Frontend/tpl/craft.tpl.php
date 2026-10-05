<?php

use Models\ProficiencyMapper;

$proficiencies = ProficiencyMapper::getAll()->executeGetArray();

$gathering = ['Mining', 'Woodcutting', 'Gathering', 'Fishing', 'Diving', 'Archeology', 'Stealing', 'Taming', 'Breeding'];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Crafting</h1>
    </div>
</div>

<div class="floater page-section">
    <div class="layout-halves">
        <div class="panel">
            <h2>Gathering</h2>
            <ul class="list">
            <?php foreach ($proficiencies as $proficiency) : ?>
                <?php if (\in_array($proficiency->name, $gathering)) : ?>
                <li><a href="/wiki/proficiency"><?= $this->printHtml($proficiency->name); ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
            </ul>
        </div>
        <div class="panel">
            <h2>Crafting</h2>
            <ul class="list">
            <?php foreach ($proficiencies as $proficiency) : ?>
                <?php if (!\in_array($proficiency->name, $gathering)) : ?>
                <li><a href="/wiki/proficiency"><?= $this->printHtml($proficiency->name); ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<div class="floater page-section">
    <div class="panel">
        <h2>Recipe search</h2>
        <form class="filter-bar" method="GET" action="/database">
            <input type="text" name="q" placeholder="Item name, e.g. Firewood">
            <button type="submit">Search</button>
        </form>
        <p class="muted">Recipes are shown on the item pages in the <a href="/database">database</a>.</p>

        <h3>Tools</h3>
        <p>Gathering requires the right tool - a shovel, pickaxe, axe, sickle, shears and more. Better tools
        gather faster and have a chance for additional materials.</p>
    </div>
</div>
