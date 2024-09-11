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
 * Faq class.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Faq
{
    public int $id = 0;

    public int $order = 0;

    public string $milestone = '';

    public string $category = '';

    public string $question = '';

    public string $answer = '';

    public \DateTime $datetime;
}