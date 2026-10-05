<?php
use Models\PlayerClassMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$classes = PlayerClassMapper::getAll()->executeGetArray();

$time = time();
?>
<h1>Classes</h1>

<p>Every character starts with one of five base classes: <strong>Warrior</strong>, <strong>Outcast</strong>,
<strong>Ranger</strong>, <strong>Caster</strong> or <strong>Summoner</strong>. As you progress you choose a
specialization that defines your play style, the equipment you can use and your role in a group.</p>

<p>Use the <a href="/build/planner">build planner</a> to theorycraft your character or browse the
<a href="/build/list">community builds</a> for inspiration.</p>

<?php foreach ($classes as $category) : ?>
    <?php if ($category->parent === null
        && ($category->releaseDate === null || $category->releaseDate->getTimestamp() < $time)) : ?>
        <h2><?= $this->printHtml($category->name); ?></h2>
        <p><?= Markdown::parse($category->description); ?></p>

        <?php foreach ($classes as $cl) : ?>
            <?php if ($cl->parent === $category->id
                && ($cl->releaseDate === null || $cl->releaseDate->getTimestamp() < $time)) : ?>
                <h3><?= $this->printHtml($cl->name); ?></h3>
                <p><?= Markdown::parse($cl->description); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endforeach; ?>