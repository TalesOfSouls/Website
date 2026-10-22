<?php
/**
 * Single feedback incl. developer response, comments and votes
 *
 * Votes and comments are sent to the API (/api/feedback/...).
 * With JavaScript the forms are submitted by jsOMS and the page is updated in place,
 * without JavaScript the API redirects back to this page.
 */
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
$feedback = $this->data['feedback'] ?? null;

if ($feedback === null) {
    include __DIR__ . '/error/404.tpl.php';
    return;
}

$signedIn = ($this->data['account'] ?? 0) > 0;

/** @var \Models\Account $viewer */
$viewer    = $this->data['viewer'] ?? new \Models\NullAccount();
$moderator = $viewer->isDeveloper();
$isOwner   = $signedIn && $feedback['author']['id'] === $viewer->id;
$canEdit   = $moderator || ($isOwner && $feedback['state'] === 'open');
$csrf     = UriFactory::getQuery('$CSRF') ?? '';
$url      = '/feedback/' . $feedback['id'];

$authorHtml = function (array $author) : string {
    $name = $this->printHtml($author['name']);
    $html = $author['link'] !== '' ? '<a href="' . $author['link'] . '">' . $name . '</a>' : '<span>' . $name . '</span>';

    return $html . ($author['developer'] ? ' <span class="badge planned">Developer</span>' : '');
};

$paragraphs = fn (string $text) : string => '<p>' . \implode('</p><p>', \array_map(
    fn ($p) => \nl2br($this->printHtml($p)),
    \preg_split('/\n\s*\n/', \trim($text))
)) . '</p>';

/**
 * Up/down vote buttons
 *
 * Every button is its own small form (jsOMS submits all named elements of a form).
 *
 * @param string $kind    feedback|comment
 * @param int    $id      Feedback or comment id
 * @param string $current Vote of the current user (up|down|'')
 * @param int    $value   Score shown between the buttons
 */
$voteBox = function (string $kind, int $id, string $current, int $value) use ($csrf, $url) : string {
    $action = $kind === 'feedback' ? '/api/feedback/vote' : '/api/feedback/comment/vote';
    $label  = $kind === 'feedback' ? 'this feedback' : 'comment';
    $html   = '<div class="vote-box' . ($kind === 'comment' ? ' small' : '') . '" data-vote="' . $kind . '-' . $id . '">';

    foreach (['up' => '&#9650;', 'down' => '&#9660;'] as $direction => $arrow) {
        $formId = 'vote-' . $kind . '-' . $id . '-' . $direction;

        $html .= '<form id="' . $formId . '" method="POST" action="' . $action . '">'
            . '<input type="hidden" name="CSRF" value="' . $csrf . '">'
            . '<input type="hidden" name="' . $kind . '" value="' . $id . '">'
            . '<input type="hidden" name="vote" value="' . $direction . '">'
            . '<input type="hidden" name="redirect" value="' . $url . '">'
            . '<button type="submit" class="vote-' . $direction . ($current === $direction ? ' voted' : '') . '" title="' . \ucfirst($direction) . 'vote ' . $label . '" aria-label="' . \ucfirst($direction) . 'vote ' . $label . '">' . $arrow . '</button>'
            . '</form>';

        if ($direction === 'up') {
            $html .= '<span class="vote-score' . ($value < 0 ? ' negative' : '') . '">' . ($value > 0 ? '+' : '') . $value . '</span>';
        }
    }

    return $html . '</div>';
};

$comments = $feedback['comments'];
$upShare  = $feedback['up'] + $feedback['down'] > 0 ? $feedback['up'] / ($feedback['up'] + $feedback['down']) * 100 : 0;
?>
<div class="floater">
    <div class="profile-banner feedback-banner">
        <p class="profile-back"><a href="/feedback">Feedback</a></p>
        <h1 class="profile-name"><?= $this->printHtml($feedback['title']); ?></h1>
        <p class="profile-meta">
            <span class="badge <?= $feedback['state']; ?>"><?= $feedback['state_title']; ?></span>
            &middot; <?= $this->printHtml($feedback['type']); ?>
            &middot; by <?= $authorHtml($feedback['author']); ?>
            &middot; <?= $feedback['date']->format('Y-m-d'); ?>
        </p>
    </div>
</div>

<div class="floater page-section">
    <?php if (!$signedIn) : ?>
        <div class="notice info"><a href="/signin?redirect=<?= \urlencode($url); ?>">Sign in</a> to vote and comment.</div>
    <?php endif; ?>

    <div class="feedback-layout">
        <div>
            <div class="panel feedback-post">
                <?= $voteBox('feedback', $feedback['id'], $feedback['vote'], $feedback['score']); ?>
                <div class="feedback-body prose">
                    <?= $paragraphs($feedback['text']); ?>
                </div>
            </div>

            <?php if ($canEdit) : ?>
            <details class="panel feedback-edit">
                <summary>Edit feedback</summary>
                <form id="feedback-edit-form" class="site-form wide" method="POST" action="/api/feedback/update">
                    <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                    <input type="hidden" name="feedback" value="<?= $feedback['id']; ?>">
                    <input type="hidden" name="redirect" value="<?= $url; ?>">
                    <div class="form-group">
                        <label for="feedback-edit-title">Title</label>
                        <input id="feedback-edit-title" type="text" name="title" minlength="5" maxlength="120" value="<?= $this->printHtml($feedback['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="feedback-edit-description">Description</label>
                        <textarea id="feedback-edit-description" name="description" minlength="20" maxlength="5000" required><?= $this->printHtml($feedback['text']); ?></textarea>
                    </div>
                    <button type="submit" class="featured">Save</button>
                </form>
            </details>
            <?php endif; ?>

            <?php if ($moderator) : ?>
            <div class="panel feedback-moderation">
                <h2>Moderation</h2>
                <form id="feedback-moderate-form" class="site-form wide" method="POST" action="/api/feedback/moderate">
                    <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                    <input type="hidden" name="feedback" value="<?= $feedback['id']; ?>">
                    <input type="hidden" name="redirect" value="<?= $url; ?>">
                    <div class="form-group">
                        <label for="feedback-state">State</label>
                        <select id="feedback-state" name="state">
                            <?php foreach (\Application\Frontend\Provider\FeedbackData::STATES as $key => $title) : ?>
                                <option value="<?= $key; ?>"<?= $key === $feedback['state'] ? ' selected' : ''; ?>><?= $title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="feedback-response">Developer response</label>
                        <textarea id="feedback-response" name="response" maxlength="5000"><?= $this->printHtml($feedback['response']['text'] ?? ''); ?></textarea>
                        <span class="form-hint">Shown above the comments. Leave it empty to remove the response.</span>
                    </div>
                    <button type="submit" class="featured">Save</button>
                </form>
                <form id="feedback-delete-form" class="inline-form" method="POST" action="/api/feedback/delete" data-confirm="Delete this feedback incl. all votes and comments?">
                    <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                    <input type="hidden" name="feedback" value="<?= $feedback['id']; ?>">
                    <button type="submit" class="danger">Delete feedback</button>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($feedback['response'] !== null) : ?>
            <div class="panel feedback-response">
                <h2>Developer response</h2>
                <p class="comment-meta"><?= $authorHtml($feedback['response']['author']); ?> &middot; <?= $feedback['response']['date']->format('Y-m-d H:i'); ?></p>
                <?= $paragraphs($feedback['response']['text']); ?>
            </div>
            <?php endif; ?>

            <div class="panel" id="comments">
                <h2>Comments <span class="muted">(<span data-comment-count><?= \count($comments); ?></span>)</span></h2>

                <p class="muted" data-no-comments<?= empty($comments) ? '' : ' hidden'; ?>>No comments yet. Be the first to comment.</p>

                <ol class="comment-list" id="comment-list">
                <?php foreach ($comments as $comment) : ?>
                    <li class="comment<?= $comment['author']['developer'] ? ' developer' : ''; ?><?= $comment['score'] <= -5 ? ' downvoted' : ''; ?>" id="comment-<?= $comment['id']; ?>">
                        <?= $voteBox('comment', $comment['id'], $comment['vote'], $comment['score']); ?>
                        <div class="comment-body">
                            <p class="comment-meta"><?= $authorHtml($comment['author']); ?> &middot; <?= $comment['date']->format('Y-m-d H:i'); ?></p>
                            <?= $paragraphs($comment['text']); ?>
                            <?php if ($moderator || ($signedIn && $comment['author']['id'] === $viewer->id)) : ?>
                            <form id="comment-delete-<?= $comment['id']; ?>" class="inline-form comment-actions" method="POST" action="/api/feedback/comment/delete" data-confirm="Delete this comment?">
                                <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                                <input type="hidden" name="comment" value="<?= $comment['id']; ?>">
                                <button type="submit" class="link-button">Delete</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ol>

                <?php if ($signedIn) : ?>
                <template id="comment-tpl">
                    <li class="comment own">
                        <div class="vote-box small"><span class="vote-score">0</span></div>
                        <div class="comment-body">
                            <p class="comment-meta"><span data-tpl="author"></span> &middot; <span data-tpl="date"></span></p>
                            <p data-tpl="text"></p>
                            <form class="inline-form comment-actions" method="POST" action="/api/feedback/comment/delete" data-confirm="Delete this comment?" data-tpl="delete">
                                <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                                <input type="hidden" name="comment" value="">
                                <input type="hidden" name="redirect" value="<?= $url; ?>#comments">
                                <button type="submit" class="link-button">Delete</button>
                            </form>
                        </div>
                    </li>
                </template>

                <form id="comment-form" class="site-form wide comment-form" method="POST" action="/api/feedback/comment">
                    <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                    <input type="hidden" name="feedback" value="<?= $feedback['id']; ?>">
                    <input type="hidden" name="redirect" value="<?= $url; ?>#comments">
                    <div class="form-group">
                        <label for="comment-text">Write a comment</label>
                        <textarea id="comment-text" name="text" maxlength="2000" required></textarea>
                        <span class="form-hint">Be constructive. Use the votes instead of "+1" comments.</span>
                    </div>
                    <button type="submit" class="featured">Post comment</button>
                </form>
                <?php else : ?>
                    <p><a class="button" href="/signin?redirect=<?= \urlencode($url . '#comments'); ?>">Sign in to comment</a></p>
                <?php endif; ?>
            </div>
        </div>

        <aside>
            <div class="panel">
                <h2>Details</h2>
                <dl class="feedback-facts">
                    <dt>State</dt><dd><span class="badge <?= $feedback['state']; ?>"><?= $feedback['state_title']; ?></span></dd>
                    <dt>Type</dt><dd><?= $this->printHtml($feedback['type']); ?></dd>
                    <dt>Upvotes</dt><dd data-vote-up="feedback-<?= $feedback['id']; ?>"><?= $feedback['up']; ?></dd>
                    <dt>Downvotes</dt><dd data-vote-down="feedback-<?= $feedback['id']; ?>"><?= $feedback['down']; ?></dd>
                    <dt>Created</dt><dd><?= $feedback['date']->format('Y-m-d'); ?></dd>
                    <dt>Last activity</dt><dd><?= $feedback['last_activity']->format('Y-m-d'); ?></dd>
                </dl>
                <?php if ($feedback['up'] + $feedback['down'] > 0) : ?>
                    <div class="vote-ratio" title="<?= \round($upShare); ?>% upvotes">
                        <span style="width: <?= \round($upShare, 1); ?>%"></span>
                    </div>
                    <p class="muted chart-note"><?= \round($upShare); ?>% upvotes</p>
                <?php endif; ?>
            </div>

            <div class="panel">
                <h2>History</h2>
                <ol class="feedback-history">
                <?php foreach (\array_reverse($feedback['history']) as $step) : ?>
                    <li><span class="badge <?= $step['state']; ?>"><?= $step['title']; ?></span> <span class="muted"><?= $step['date']; ?></span></li>
                <?php endforeach; ?>
                </ol>
            </div>
        </aside>
    </div>
</div>
