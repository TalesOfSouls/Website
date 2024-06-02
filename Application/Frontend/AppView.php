<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Application\Frontend
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Application\Frontend;

use phpOMS\Localization\L11nManager;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;
use phpOMS\Model\Html\Head;

class AppView extends View
{
    /**
     * Head
     *
     * @var Head
     * @since 1.0.0
     */
    public ?Head $head = null;

    public function __construct(L11nManager $l11n = null, RequestAbstract $request = null, ResponseAbstract $response = null)
    {
        parent::__construct($l11n, $request, $response);
    }
}
