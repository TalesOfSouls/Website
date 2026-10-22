<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Models;

/**
 * Equipped items of a character (item_individual ids).
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class CharacterEquipment
{
    public int $id = 0;

    public int $character = 0;

    public ?int $head = null;

    public ?int $neck = null;

    public ?int $body = null;

    public ?int $belt = null;

    public ?int $pants = null;

    public ?int $boots = null;

    public ?int $ring1 = null;

    public ?int $ring2 = null;

    public ?int $hand1 = null;

    public ?int $hand2 = null;

    public ?int $hand1Alt = null;

    public ?int $hand2Alt = null;

    public ?int $arms = null;

    public ?int $beltAttach = null;

    public ?int $beltTrinket = null;

    public ?int $shoulder = null;

    public ?int $back = null;

    public ?int $hands = null;

    public ?int $artifact = null;

    /**
     * Constructor.
     *
     * @param int $id Model id
     *
     * @since 1.0.0
     */
    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    /**
     * Item ids per equipment slot name (see equipment_slot)
     *
     * @return array<string, int>
     *
     * @since 1.0.0
     */
    public function getItemsBySlot() : array
    {
        return \array_filter([
            'Head'            => $this->head,
            'Neck'            => $this->neck,
            'Body'            => $this->body,
            'Belt'            => $this->belt,
            'Pants'           => $this->pants,
            'Boots'           => $this->boots,
            'Ring'            => $this->ring1,
            'Hand 1'          => $this->hand1,
            'Hand 2'          => $this->hand2,
            'Arms'            => $this->arms,
            'Belt Attachment' => $this->beltAttach,
            'Shoulder'        => $this->shoulder,
            'Back'            => $this->back,
            'Artifact'        => $this->artifact,
        ]);
    }
}
