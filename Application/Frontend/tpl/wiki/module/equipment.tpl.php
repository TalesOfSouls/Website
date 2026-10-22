<?php

use Models\EquipmentTypeMapper;
use Models\EquipmentSlotMapper;

$types = EquipmentTypeMapper::getAll()->executeGetArray();
$slots = EquipmentSlotMapper::getAll()->executeGetArray();
?>
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
