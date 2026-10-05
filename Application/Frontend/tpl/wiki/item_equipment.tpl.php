<?php

use Models\EquipmentTypeMapper;
use Models\EquipmentSlotMapper;

$types = EquipmentTypeMapper::getAll()->executeGetArray();
$slots = EquipmentSlotMapper::getAll()->executeGetArray();
?>
<h1>Equipment</h1>

<p>Your character has <?= \count($slots); ?> equipment slots. Every slot accepts a set of equipment types -
which of these you can use depends on your class. Equipment rolls attributes based on its type, level and
<a href="/wiki/items">rarity</a>.</p>

<h2>Equipment Slots</h2>
<?php foreach ($slots as $slot) : ?>
    <h3><?= $this->printHtml($slot->name); ?></h3>
    <?php
        $slotTypes = \array_filter($types, fn ($type) => $type->slot === $slot->id);
    ?>
    <?php if (empty($slotTypes)) : ?>
        <p><em>No equipment types published for this slot yet.</em></p>
    <?php else : ?>
        <p><?= \implode(', ', \array_map(fn ($type) => $this->printHtml($type->name), $slotTypes)); ?></p>
    <?php endif; ?>
<?php endforeach; ?>
