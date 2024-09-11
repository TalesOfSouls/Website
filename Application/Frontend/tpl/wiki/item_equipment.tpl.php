<?php

use Models\EquipmentTypeMapper;
use Models\EquipmentSlotMapper;
use phpOMS\Utils\Parser\Markdown\Markdown;

$types = EquipmentTypeMapper::getAll()->executeGetArray();
$slots = EquipmentSlotMapper::getAll()->executeGetArray();

$time = time();
?>
<h1>Equipment</h1>

<h2>Equipment Slots</h2>
<?php foreach ($slots as $slot) : ?>
    <h2><?= $this->printHtml($slot->name); ?></h2>
    <p><?= '';/*Markdown::parse($slot->description);*/ ?></p>

    <h2>Equipment Types</h2>
    <?php foreach ($types as $type) : ?>
        <?php if ($type->slot === $slot->id) : ?>
            <h2><?= $this->printHtml($type->name); ?></h2>
            <p><?= '';/*Markdown::parse($type->description);*/ ?></p>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endforeach; ?>
