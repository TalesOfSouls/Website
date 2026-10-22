<?php
/**
 * Tales of Souls
 *
 * PHP Version 8.2
 *
 * @package   Application\Frontend\Provider
 * @version   1.0.0
 */
declare(strict_types=1);

namespace Application\Frontend\Provider;

use Models\CharacterEquipment;
use Models\CharacterEquipmentMapper;
use Models\CharacterEventMapper;
use Models\CharacterEventType;
use Models\CharacterLevelLogMapper;
use Models\CharacterLog;
use Models\CharacterLogMapper;
use Models\CharacterProficiency;
use Models\CharacterProficiencyMapper;
use Models\EquipmentSlotMapper;
use Models\ItemIndividualMapper;

/**
 * Equipment, proficiencies, level progression and event log of a character
 *
 * @package Application\Frontend\Provider
 * @since   1.0.0
 */
final class CharacterData extends DataAbstract
{
    /**
     * Event types (key => title)
     *
     * @var array<string, string>
     * @since 1.0.0
     */
    public const EVENT_TYPES = [
        CharacterEventType::LEVEL       => 'Level up',
        CharacterEventType::PROFESSION  => 'Profession',
        CharacterEventType::DUNGEON     => 'Dungeon',
        CharacterEventType::RAID        => 'Raid',
        CharacterEventType::WORLDBOSS   => 'World boss',
        CharacterEventType::PVP         => 'PvP',
        CharacterEventType::ACHIEVEMENT => 'Achievement',
        CharacterEventType::LOOT        => 'Loot',
    ];

    /**
     * Level progression (oldest first)
     *
     * @param int $character Character id
     *
     * @return array<int, array{level:int, hours:float, date:\DateTime}>
     *
     * @since 1.0.0
     */
    public function levelProgression(int $character) : array
    {
        $progression = [];
        foreach (CharacterLevelLogMapper::getAll()->where('character', $character)->sort('level', 'ASC')->executeGetArray() as $log) {
            $progression[] = ['level' => $log->level, 'hours' => $log->playtime, 'date' => $log->datetime];
        }

        return $progression;
    }

    /**
     * Learned proficiencies (name => level)
     *
     * @param int $character Character id
     *
     * @return array<string, int>
     *
     * @since 1.0.0
     */
    public function professions(int $character) : array
    {
        /** @var CharacterProficiency $proficiency */
        $proficiency = CharacterProficiencyMapper::get()->where('character', $character)->execute();

        return $proficiency->id === 0 ? [] : $proficiency->getLearned();
    }

    /**
     * Statistics of a character
     *
     * @param int $character Character id
     *
     * @return null|CharacterLog
     *
     * @since 1.0.0
     */
    public function statistics(int $character) : ?CharacterLog
    {
        /** @var CharacterLog $log */
        $log = CharacterLogMapper::get()->where('character', $character)->execute();

        return $log->id === 0 ? null : $log;
    }

    /**
     * Equipped items (slot id => name, rarity, level)
     *
     * @param int $character Character id
     *
     * @return array<int, array{name:string, rarity:string, level:int}>
     *
     * @since 1.0.0
     */
    public function equipment(int $character) : array
    {
        /** @var CharacterEquipment $equipment */
        $equipment = CharacterEquipmentMapper::get()->where('character', $character)->execute();
        if ($equipment->id === 0) {
            return [];
        }

        $bySlot = $equipment->getItemsBySlot();
        if (empty($bySlot)) {
            return [];
        }

        $ids   = \array_values($bySlot);
        $items = [];
        foreach (ItemIndividualMapper::getAll()->with('rarity')->where('id', \count($ids) === 1 ? $ids[0] : $ids)->executeGetArray() as $item) {
            $items[$item->id] = $item;
        }

        $slots = [];
        foreach (EquipmentSlotMapper::getAll()->executeGetArray() as $slot) {
            $slots[$slot->name] = $slot->id;
        }

        $result = [];
        foreach ($bySlot as $slotName => $itemId) {
            if (!isset($slots[$slotName], $items[$itemId])) {
                continue;
            }

            $result[$slots[$slotName]] = [
                'name'   => $items[$itemId]->name,
                'rarity' => \strtolower($items[$itemId]->rarity->name),
                'level'  => $items[$itemId]->level,
            ];
        }

        return $result;
    }

    /**
     * Event log (newest first)
     *
     * @param int    $character Character id
     * @param int    $account   Account id of the character (for the links)
     * @param string $type      Event type filter ('' = all)
     * @param int    $page      Page (1 = first)
     * @param int    $perPage   Events per page
     *
     * @return array{events:array, count:int}
     *
     * @since 1.0.0
     */
    public function events(int $character, int $account, string $type, int $page, int $perPage) : array
    {
        $mapper = CharacterEventMapper::getAll()->where('character', $character);
        $count  = CharacterEventMapper::count()->where('character', $character);

        if ($type !== '') {
            $mapper->where('type', $type);
            $count->where('type', $type);
        }

        $rarities = [];
        foreach ($this->rows('SELECT item_rarity_id, item_rarity_name FROM item_rarity') as $row) {
            $rarities[(int) $row['item_rarity_id']] = \strtolower((string) $row['item_rarity_name']);
        }

        $events = [];
        foreach ($mapper->sort('datetime', 'DESC')->limit($perPage)->offset(($page - 1) * $perPage)->executeGetArray() as $event) {
            $events[] = [
                'date'   => $event->datetime,
                'type'   => $event->type,
                'title'  => $event->title,
                'detail' => $event->detail,
                'link'   => $event->encounter === null ? '' : '/player/profile/' . $account . '/' . $event->type . '/' . $event->encounter,
                'rarity' => $event->rarity === null ? '' : ($rarities[$event->rarity] ?? ''),
            ];
        }

        return ['events' => $events, 'count' => $count->executeCount()];
    }
}
