<?php

use Models\ItemTypeMapper;
use Models\ItemRarityMapper;
use Models\ItemMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$types = ItemTypeMapper::getAll()->executeGetArray();
$rarities = ItemRarityMapper::getAll()->executeGetArray();
$items = ItemMapper::getAll()->executeGetArray();

$time = time();
?>
<h1>Item</h1>

<h2>Item Rarity</h2>
<?php foreach ($rarities as $rarity) : ?>
    <h2><?= $this->printHtml($rarity->name); ?></h2>
<?php endforeach; ?>


<h2>Item Types</h2>
<?php foreach ($types as $type) : ?>
    <?php if ($type->releaseDate === null || $type->releaseDate->getTimestamp() < $time) : ?>
        <h2><?= $this->printHtml($type->name); ?></h2>
        <p><?= Markdown::parse($type->description); ?></p>
    <?php endif; ?>
<?php endforeach; ?>

<h2>Items</h2>
<p></p>
    <?php foreach ($items as $item) : ?>
        <a href="/wiki/item?id=<?= $item->id; ?>"><?= $this->printHtml($item->name); ?></a>
    <?php endforeach; ?>
</p>