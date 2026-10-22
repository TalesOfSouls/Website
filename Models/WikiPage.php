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
 * Wiki article.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class WikiPage
{
    public int $id = 0;

    /**
     * Url of the article (/wiki/{slug}), "index" is the wiki overview
     *
     * @var string
     * @since 1.0.0
     */
    public string $slug = '';

    public string $title = '';

    public string $navTitle = '';

    /**
     * Markdown (html is allowed)
     *
     * @var string
     * @since 1.0.0
     */
    public string $content = '';

    /**
     * Data block shown below the article (see tpl/wiki/module)
     *
     * @var ?string
     * @since 1.0.0
     */
    public ?string $module = null;

    /**
     * Position in the wiki navigation (null = not shown)
     *
     * @var ?int
     * @since 1.0.0
     */
    public ?int $navOrder = null;

    public Account $account;

    public \DateTime $updated;

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
        $this->account = new NullAccount();
        $this->updated = new \DateTime();
    }
}
