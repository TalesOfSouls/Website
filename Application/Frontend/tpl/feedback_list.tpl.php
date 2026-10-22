<?php
use Application\Frontend\SampleFeedback;

/** @var \phpOMS\Views\View $this */
$feedbacks = $this->data['feedbacks'] ?? [];
$query     = $this->data['query'] ?? '';
$type      = $this->data['type'] ?? '';
$state     = $this->data['state'] ?? '';
$sort      = $this->data['sort'] ?? 'top';

$sortUrl = function (string $value) use ($query, $type, $state) : string {
    $params = \array_filter(['q' => $query, 'type' => $type, 'state' => $state, 'sort' => $value === 'top' ? '' : $value]);

    return '/feedback' . (empty($params) ? '' : '?' . \htmlspecialchars(\http_build_query($params)));
};
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Feedback</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/feedback">
        <input type="text" name="q" placeholder="Search feedback" value="<?= $this->printHtml($query); ?>">
        <select name="type">
            <option value="">Any type</option>
            <?php foreach (SampleFeedback::TYPES as $t) : ?>
                <option<?= $t === $type ? ' selected' : ''; ?>><?= $t; ?></option>
            <?php endforeach; ?>
        </select>
        <select name="state">
            <option value="">Any state</option>
            <?php foreach (SampleFeedback::STATES as $key => $title) : ?>
                <option value="<?= $key; ?>"<?= $key === $state ? ' selected' : ''; ?>><?= $title; ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($sort !== 'top') : ?><input type="hidden" name="sort" value="<?= $sort; ?>"><?php endif; ?>
        <button type="submit">Filter</button>
        <a class="button featured" href="/feedback/create">Submit feedback</a>
    </form>

    <div class="floater">
        <nav class="filter-chips feedback-sort" aria-label="Sort">
            <?php foreach (['top' => 'Top voted', 'new' => 'Newest', 'comments' => 'Most comments'] as $key => $title) : ?>
                <a href="<?= $sortUrl($key); ?>"<?= $sort === $key ? ' class="active"' : ''; ?>><?= $title; ?></a>
            <?php endforeach; ?>
        </nav>

        <table class="content-table encounter-table feedback-table">
            <thead>
                <tr>
                    <th>Votes</th>
                    <th class="wf-100">Title</th>
                    <th>Type</th>
                    <th>State</th>
                    <th>Comments</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $feedback) : ?>
                <tr>
                    <td class="feedback-score<?= $feedback['score'] < 0 ? ' negative' : ''; ?>" title="<?= $feedback['up']; ?> up, <?= $feedback['down']; ?> down"><?= $feedback['score'] > 0 ? '+' : ''; ?><?= $feedback['score']; ?></td>
                    <td>
                        <a class="row-link" href="/feedback/<?= $feedback['id']; ?>"><?= $this->printHtml($feedback['title']); ?></a>
                        <small class="muted feedback-author">by <?= $this->printHtml($feedback['author']['name']); ?></small>
                    </td>
                    <td><?= $feedback['type']; ?></td>
                    <td><span class="badge <?= $feedback['state']; ?>"><?= $feedback['state_title']; ?></span></td>
                    <td><?= $feedback['comment_count']; ?></td>
                    <td class="nowrap"><?= $feedback['date']->format('Y-m-d'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($feedbacks)) : ?>
                <tr><td colspan="6">No feedback found. <a href="/feedback/create">Submit your own</a>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
