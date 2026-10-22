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
 * News post.
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class News
{
    public int $id = 0;

    public string $slug = '';

    public string $title = '';

    /**
     * Short text for the news lists
     *
     * @var string
     * @since 1.0.0
     */
    public string $summary = '';

    /**
     * Image url (e.g. /Application/Frontend/data/news/img/...)
     *
     * @var string
     * @since 1.0.0
     */
    public string $image = '';

    /**
     * Markdown
     *
     * @var string
     * @since 1.0.0
     */
    public string $content = '';

    public Account $account;

    /**
     * Status (see NewsStatus)
     *
     * @var string
     * @since 1.0.0
     */
    public string $status = NewsStatus::DRAFT;

    public \DateTime $published;

    public \DateTime $created;

    public ?\DateTime $updated = null;

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
        $this->published = new \DateTime();
        $this->created = new \DateTime();
    }

    /**
     * Is the post visible on the website?
     *
     * @param null|\DateTimeInterface $now Current date
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isVisible(?\DateTimeInterface $now = null) : bool
    {
        return $this->status === NewsStatus::PUBLISHED && $this->published <= ($now ?? new \DateTime());
    }
}
