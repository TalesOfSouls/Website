<?php
/** @var \phpOMS\Views\View $this */
$counts = $this->data['counts'] ?? [];

/** @var \Models\Feedback[] $feedback */
$feedback = $this->data['feedback'] ?? [];

/** @var \Models\ContactMessage[] $messages */
$messages = $this->data['messages'] ?? [];

/** @var \Models\Account[] $accounts */
$accounts = $this->data['accounts'] ?? [];

include __DIR__ . '/nav.tpl.php';
?>
    <div class="stat-tiles">
        <?php foreach ($counts as $label => $count) : ?>
            <div class="stat-tile"><span class="value"><?= \number_format($count); ?></span><span class="label"><?= $label; ?></span></div>
        <?php endforeach; ?>
    </div>

    <div class="layout-halves">
        <div class="panel">
            <h2>Latest feedback</h2>
            <table class="content-table encounter-table">
                <thead><tr><th class="wf-100">Title</th><th>State</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($feedback as $entry) : ?>
                    <tr>
                        <td><a class="row-link" href="/feedback/<?= $entry->id; ?>"><?= $this->printHtml($entry->title); ?></a> <small class="muted">by <?= $this->printHtml($entry->account->name); ?></small></td>
                        <td><span class="badge <?= $this->printHtml($entry->state); ?>"><?= \Application\Frontend\Provider\FeedbackData::STATES[$entry->state] ?? $entry->state; ?></span></td>
                        <td class="nowrap"><?= $entry->created->format('Y-m-d'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div>
            <div class="panel">
                <h2>Create</h2>
                <p class="admin-actions">
                    <a class="button featured" href="/admin/news/create">News post</a>
                    <a class="button" href="/admin/wiki/create">Wiki article</a>
                    <a class="button" href="/admin/faq/create">FAQ entry</a>
                    <a class="button" href="/admin/development">Milestone</a>
                </p>
            </div>
            <div class="panel">
                <h2>Latest messages</h2>
                <dl class="stat-list">
                    <?php foreach ($messages as $message) : ?>
                        <dt><?= $this->printHtml($message->subject !== '' ? $message->subject : 'Message'); ?> <small class="muted">from <?= $this->printHtml($message->name); ?></small></dt>
                        <dd><?= $message->created->format('Y-m-d'); ?></dd>
                    <?php endforeach; ?>
                    <?php if (empty($messages)) : ?>
                        <dt>No messages yet</dt><dd></dd>
                    <?php endif; ?>
                </dl>
                <p><a href="/admin/messages">All messages</a></p>
            </div>
            <div class="panel">
                <h2>New accounts</h2>
                <dl class="stat-list">
                    <?php foreach ($accounts as $account) : ?>
                        <dt><a href="/player/profile/<?= $account->id; ?>"><?= $this->printHtml($account->name); ?></a></dt>
                        <dd><?= $account->created?->format('Y-m-d') ?? '-'; ?></dd>
                    <?php endforeach; ?>
                </dl>
                <p><a href="/admin/users">All accounts</a></p>
            </div>
        </div>
    </div>
</div>
