<?php

use Models\ProficiencyMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$proficiencies = ProficiencyMapper::getAll()->executeGetArray();

$time = time();
?>
<h1>Proficiencies</h1>

<?php foreach ($proficiencies as $proficiency) : ?>
    <?php if ($proficiency->releaseDate === null || $proficiency->releaseDate->getTimestamp() < $time) : ?>
        <h2><?= $this->printHtml($proficiency->name); ?></h2>
        <p><?= Markdown::parse($proficiency->description); ?></p>
    <?php endif; ?>
<?php endforeach; ?>