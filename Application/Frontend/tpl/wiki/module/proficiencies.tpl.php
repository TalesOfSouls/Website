<?php

use Models\ProficiencyMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$proficiencies = ProficiencyMapper::getAll()->executeGetArray();

$time = time();
?>
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