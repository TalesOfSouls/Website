<?php

use Models\RaceMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$races = RaceMapper::getAll()->executeGetArray();

$time = time();
?>
<h1>Races</h1>

<?php foreach ($races as $race) : ?>
    <?php if ($race->releaseDate === null || $race->releaseDate->getTimestamp() < $time) : ?>
        <h2><?= $this->printHtml($race->name); ?></h2>
        <p><?= Markdown::parse($race->description); ?></p>
    <?php endif; ?>
<?php endforeach; ?>