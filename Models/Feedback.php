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
 * Feedback (bug report, suggestion, ...).
 *
 * @package Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Feedback
{
    public int $id = 0;

    public Account $account;

    /**
     * Type (see FeedbackType)
     *
     * @var string
     * @since 1.0.0
     */
    public string $type = '';

    /**
     * State (see FeedbackState)
     *
     * @var string
     * @since 1.0.0
     */
    public string $state = FeedbackState::OPEN;

    public string $title = '';

    public string $text = '';

    public \DateTime $created;

    public ?\DateTime $updated = null;

    /**
     * Developer response
     *
     * @var ?string
     * @since 1.0.0
     */
    public ?string $response = null;

    public Account $responseAccount;

    public ?\DateTime $responseDatetime = null;

    /**
     * Comments
     *
     * @var FeedbackComment[]
     * @since 1.0.0
     */
    public array $comments = [];

    /**
     * State changes
     *
     * @var FeedbackStateLog[]
     * @since 1.0.0
     */
    public array $history = [];

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
        $this->created = new \DateTime();
        $this->responseAccount = new NullAccount();
    }
}
