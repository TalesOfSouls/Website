<?php

use Models\RaceMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$races = RaceMapper::getAll()->executeGetArray();

$time = time();
?>
<?php foreach ($races as $race) : ?>
    <?php if ($race->releaseDate === null || $race->releaseDate->getTimestamp() < $time) : ?>
        <h2><?= $this->printHtml($race->name); ?></h2>
        <?php if ($race->description !== '') : ?>
            <p><?= Markdown::parse($race->description); ?></p>
        <?php else : ?>
            <p><em>A detailed description of this race will follow.</em></p>
        <?php endif; ?>
    <?php endif; ?>
<?php endforeach; ?>
