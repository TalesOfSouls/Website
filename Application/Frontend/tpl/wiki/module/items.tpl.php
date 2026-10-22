<?php

use Models\ItemTypeMapper;
use Models\ItemRarityMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$types    = ItemTypeMapper::getAll()->executeGetArray();
$rarities = ItemRarityMapper::getAll()->executeGetArray();

$time = time();
?>
<h2>Item Rarity</h2>
<p>The rarity of an item is shown by the color of its name. Higher rarities drop less often but roll more
and stronger attributes. The highest rarities can't be traded and must be earned.</p>
<table>
    <thead>
        <tr>
            <th>Rarity</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($rarities as $rarity) : ?>
        <tr>
            <td class="rarity-<?= \strtolower($this->printHtml($rarity->name)); ?>"><strong><?= $this->printHtml($rarity->name); ?></strong></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Item Types</h2>
<table>
    <thead>
        <tr>
            <th>Type</th>
            <th>Tradable</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($types as $type) : ?>
        <?php if ($type->releaseDate === null || $type->releaseDate->getTimestamp() < $time) : ?>
        <tr>
            <td><?= $this->printHtml($type->name); ?></td>
            <td><?= $type->tradable ? 'Yes' : 'No'; ?></td>
        </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Equipment</h2>
<p>Equipment is worn in one of the equipment slots. Read more about <a href="/wiki/item/equipment">equipment slots and types</a>.</p>
