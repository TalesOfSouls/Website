<?php
use Models\PlayerClassMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$classes = PlayerClassMapper::getAll()->executeGetArray();

$time = time();
?>
<h1>Classes</h1>

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