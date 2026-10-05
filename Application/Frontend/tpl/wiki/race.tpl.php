<?php

use Models\RaceMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$races = RaceMapper::getAll()->executeGetArray();

$time = time();
?>
<h1>Races</h1>

<p>The world of Tales of Souls is home to many peoples. Every race comes with its own history, homeland,
affiliations and appearance options. Your race shapes how NPCs react to you and which regions consider you
a friend - but it never locks you out of a class.</p>

<p>Most races belong to one of the great families: <strong>Humans</strong>, <strong>Dwarves</strong>,
<strong>Elves</strong>, <strong>Beasts</strong>, the cursed bloodlines of the <strong>Styx</strong>,
the <strong>Fellborn</strong> and the reptilian <strong>Veyssar</strong>.</p>

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
