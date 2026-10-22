<?php
/** @var \phpOMS\Views\View $this */

/** @var \Models\ContactMessage[] $messages */
$messages = $this->data['messages'] ?? [];

include __DIR__ . '/nav.tpl.php';
?>
    <div class="panel">
        <h2>Contact messages</h2>
        <?php foreach ($messages as $message) : ?>
            <details class="admin-message">
                <summary>
                    <span class="nowrap"><?= $message->created->format('Y-m-d H:i'); ?></span>
                    <strong><?= $this->printHtml($message->subject !== '' ? $message->subject : 'Message'); ?></strong>
                    <span class="muted">from <?= $this->printHtml($message->name); ?></span>
                </summary>
                <p class="comment-meta">
                    <a href="mailto:<?= $this->printHtml($message->email); ?>"><?= $this->printHtml($message->email); ?></a>
                    <?php if ($message->account !== null) : ?>&middot; <a href="/player/profile/<?= $message->account; ?>">Account #<?= $message->account; ?></a><?php endif; ?>
                </p>
                <p class="admin-message-text"><?= \nl2br($this->printHtml($message->text)); ?></p>
            </details>
        <?php endforeach; ?>
        <?php if (empty($messages)) : ?>
            <p class="muted">No messages yet.</p>
        <?php endif; ?>
    </div>
</div>
