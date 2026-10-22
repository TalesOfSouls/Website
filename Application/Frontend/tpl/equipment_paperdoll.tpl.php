<?php
/**
 * Equipment slots arranged around the 3D character view
 *
 * Expects:
 *   $slots       \Models\EquipmentSlot[] Equipment slots
 *   $equipment   array                   Equipped items (slot id => ['name', 'rarity', 'level'])
 *   $viewCaption string                  Caption below the 3D view (already escaped)
 *   $emptyText   string                  Text for empty slots (optional)
 */

/** @var \phpOMS\Views\View $this */

// Position of each equipment slot around the 3D view
$slotPositions = [
    'Head' => 'top', 'Neck' => 'top', 'Shoulder' => 'top',
    'Back' => 'left', 'Body' => 'left', 'Arms' => 'left',
    'Belt' => 'right', 'Belt Attachment' => 'right', 'Pants' => 'right', 'Boots' => 'right',
    'Hand 1' => 'bottom', 'Ring' => 'bottom', 'Artifact' => 'bottom', 'Hand 2' => 'bottom',
];

$slotGroups = ['top' => [], 'left' => [], 'right' => [], 'bottom' => []];
foreach ($slots as $slot) {
    $slotGroups[$slotPositions[$slot->name] ?? 'bottom'][] = $slot;
}

$emptyText = $emptyText ?? 'Empty';

$renderSlot = function ($slot) use ($equipment, $emptyText) : string {
    $item = $equipment[$slot->id] ?? null;

    return '<div class="equipment-slot' . ($item === null ? ' empty' : ' rarity-border-' . $item['rarity']) . '"'
        . ($item !== null ? ' title="' . $this->printHtml($item['name']) . ' (item level ' . $item['level'] . ')"' : '') . '>'
        . '<span class="slot-name">' . $this->printHtml($slot->name) . '</span>'
        . ($item === null
            ? '<span class="slot-item muted">' . $this->printHtml($emptyText) . '</span>'
            : '<span class="slot-item rarity-' . $item['rarity'] . '">' . $this->printHtml($item['name']) . '</span>'
                . '<span class="slot-level">ilvl ' . $item['level'] . '</span>')
        . '</div>';
};
?>
<div class="paperdoll">
    <div class="paperdoll-top">
        <?php foreach ($slotGroups['top'] as $slot) { echo $renderSlot($slot); } ?>
    </div>
    <div class="paperdoll-left">
        <?php foreach ($slotGroups['left'] as $slot) { echo $renderSlot($slot); } ?>
    </div>
    <div class="paperdoll-view" title="3D character preview">
        <span class="view-corner tl"></span><span class="view-corner tr"></span>
        <span class="view-corner bl"></span><span class="view-corner br"></span>
        <svg class="view-figure" viewBox="0 0 120 220" aria-hidden="true">
            <circle cx="60" cy="28" r="17"/>
            <path d="M38 52 Q60 44 82 52 L92 110 Q86 114 80 110 L76 82 L74 128 L80 206 Q72 212 64 206 L60 140 L56 206 Q48 212 40 206 L46 128 L44 82 L40 110 Q34 114 28 110 Z"/>
        </svg>
        <span class="view-label">3D preview</span>
        <span class="view-caption"><?= $viewCaption; ?></span>
    </div>
    <div class="paperdoll-right">
        <?php foreach ($slotGroups['right'] as $slot) { echo $renderSlot($slot); } ?>
    </div>
    <div class="paperdoll-bottom">
        <?php foreach ($slotGroups['bottom'] as $slot) { echo $renderSlot($slot); } ?>
    </div>
</div>
