<?php

use Models\ProficiencyMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$proficiencies = ProficiencyMapper::getAll()->executeGetArray();

$time = time();
?>
<h1>Proficiencies</h1>

<p>Proficiencies are the professions of Tales of Souls. Gathering proficiencies such as Mining, Woodcutting,
Fishing or Diving provide raw materials, while crafting proficiencies such as Forging, Tailoring, Brewing or
Cooking turn them into useful items. Every character can learn all proficiencies - but mastering them takes time,
the right tools and plenty of practice.</p>

<p>See the <a href="/craft">crafting overview</a> for recipes and tools.</p>

<?php foreach ($proficiencies as $proficiency) : ?>
    <?php if ($proficiency->releaseDate === null || $proficiency->releaseDate->getTimestamp() < $time) : ?>
        <h2><?= $this->printHtml($proficiency->name); ?></h2>
        <?php if ($proficiency->description !== '') : ?>
            <p><?= Markdown::parse($proficiency->description); ?></p>
        <?php else : ?>
            <p><em>A detailed description of this proficiency will follow.</em></p>
        <?php endif; ?>
    <?php endif; ?>
<?php endforeach; ?>