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
 * Individual (rolled) item, e.g. an equipped item.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class ItemIndividual
{
    public int $id = 0;

    public ?int $equipmentType = null;

    public string $name = '';

    public ItemRarity $rarity;

    public int $level = 0;

    public ?int $character = null;

    public ?\DateTime $created = null;

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
        $this->rarity = new NullItemRarity();
    }
}
