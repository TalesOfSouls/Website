<?php
/**
 * Single feedback incl. developer response, comments and votes
 *
 * Voting and commenting use plain forms (no JavaScript).
 */
use Application\Frontend\SampleFeedback;

/** @var \phpOMS\Views\View $this */
$feedback = $this->data['feedback'] ?? null;

if ($feedback === null) {
    include __DIR__ . '/error/404.tpl.php';
    return;
}

$vote         = $this->data['vote'] ?? '';
$commentVotes = $this->data['commentVotes'] ?? [];
$newComment   = $this->data['newComment'] ?? null;
$url          = '/feedback/' . $feedback['id'];

// the vote of the current request is added to the sample counts
$up    = $feedback['up'] + ($vote === 'up' ? 1 : 0);
$down  = $feedback['down'] + ($vote === 'down' ? 1 : 0);
$score = $up - $down;

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
 * @param string $action  vote|comment-vote
 * @param string $current Vote of the current user
 * @param int    $value   Score shown between the buttons
 * @param array  $extra   Additional hidden fields
 */
$voteForm = function (string $action, string $current, int $value, string $label, array $extra = []) use ($url) : string {
    $hidden = '<input type="hidden" name="action" value="' . $action . '">';
    foreach ($extra as $name => $fieldValue) {
        $hidden .= '<input type="hidden" name="' . $name . '" value="' . $fieldValue . '">';
    }

    return '<form class="vote-box' . ($action === 'comment-vote' ? ' small' : '') . '" method="POST" action="' . $url . '">' . $hidden
        . '<button type="submit" name="vote" value="up" class="vote-up' . ($current === 'up' ? ' voted' : '') . '" title="Upvote ' . $label . '" aria-label="Upvote ' . $label . '">&#9650;</button>'
        . '<span class="vote-score' . ($value < 0 ? ' negative' : '') . '">' . ($value > 0 ? '+' : '') . $value . '</span>'
        . '<button type="submit" name="vote" value="down" class="vote-down' . ($current === 'down' ? ' voted' : '') . '" title="Downvote ' . $label . '" aria-label="Downvote ' . $label . '">&#9660;</button>'
        . '</form>';
};

$comments = $feedback['comments'];
?>
<div class="floater">
    <div class="profile-banner feedback-banner">
        <p class="profile-back"><a href="/feedback">Feedback</a></p>
        <h1 class="profile-name"><?= $this->printHtml($feedback['title']); ?></h1>
        <p class="profile-meta">
            <span class="badge <?= $feedback['state']; ?>"><?= $feedback['state_title']; ?></span>
            &middot; <?= $feedback['type']; ?>
            &middot; by <?= $authorHtml($feedback['author']); ?>
            &middot; <?= $feedback['date']->format('Y-m-d'); ?>
        </p>
    </div>
</div>

<div class="floater page-section">
    <div class="notice info">Feedback, comments and votes are sample data. Votes and comments are not saved yet.</div>

    <?php if ($vote !== '') : ?>
        <div class="notice">Your <?= $vote === 'up' ? 'upvote' : 'downvote'; ?> has been counted.</div>
    <?php elseif (!empty($commentVotes)) : ?>
        <div class="notice">Your vote on the comment has been counted.</div>
    <?php elseif ($newComment !== null) : ?>
        <div class="notice">Your comment has been posted.</div>
    <?php elseif (!empty($this->data['commentError'])) : ?>
        <div class="notice error">Please write a comment (max. 2000 characters).</div>
    <?php endif; ?>

    <div class="feedback-layout">
        <div>
            <div class="panel feedback-post">
                <?= $voteForm('vote', $vote, $score, 'this feedback'); ?>
                <div class="feedback-body prose">
                    <?= $paragraphs($feedback['text']); ?>
                </div>
            </div>

            <?php if ($feedback['response'] !== null) : ?>
            <div class="panel feedback-response">
                <h2>Developer response</h2>
                <p class="comment-meta"><?= $authorHtml($feedback['response']['author']); ?> &middot; <?= $feedback['response']['date']->format('Y-m-d H:i'); ?></p>
                <?= $paragraphs($feedback['response']['text']); ?>
            </div>
            <?php endif; ?>

            <div class="panel" id="comments">
                <h2>Comments <span class="muted">(<?= \count($comments) + ($newComment !== null ? 1 : 0); ?>)</span></h2>

                <?php if (empty($comments) && $newComment === null) : ?>
                    <p class="muted">No comments yet. Be the first to comment.</p>
                <?php endif; ?>

                <ol class="comment-list">
                <?php foreach ($comments as $comment) :
                    $commentVote  = $commentVotes[$comment['id']] ?? '';
                    $commentScore = $comment['score'] + ($commentVote === 'up' ? 1 : ($commentVote === 'down' ? -1 : 0));
                ?>
                    <li class="comment<?= $comment['author']['developer'] ? ' developer' : ''; ?><?= $commentScore <= -5 ? ' downvoted' : ''; ?>" id="comment-<?= $comment['id']; ?>">
                        <?= $voteForm('comment-vote', $commentVote, $commentScore, 'comment', ['comment' => $comment['id']]); ?>
                        <div class="comment-body">
                            <p class="comment-meta"><?= $authorHtml($comment['author']); ?> &middot; <?= $comment['date']->format('Y-m-d H:i'); ?></p>
                            <?= $paragraphs($comment['text']); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if ($newComment !== null) : ?>
                    <li class="comment own" id="comment-new">
                        <div class="vote-box small"><span class="vote-score">0</span></div>
                        <div class="comment-body">
                            <p class="comment-meta"><span>You</span> &middot; <?= $newComment['date']->format('Y-m-d H:i'); ?></p>
                            <?= $paragraphs($newComment['text']); ?>
                        </div>
                    </li>
                <?php endif; ?>
                </ol>

                <form class="site-form wide comment-form" method="POST" action="<?= $url; ?>#comments">
                    <input type="hidden" name="action" value="comment">
                    <div class="form-group">
                        <label for="comment-text">Write a comment</label>
                        <textarea id="comment-text" name="text" maxlength="2000" required></textarea>
                        <span class="form-hint">Be constructive. Use the votes instead of "+1" comments.</span>
                    </div>
                    <button type="submit" class="featured">Post comment</button>
                </form>
            </div>
        </div>

        <aside>
            <div class="panel">
                <h2>Details</h2>
                <dl class="feedback-facts">
                    <dt>State</dt><dd><span class="badge <?= $feedback['state']; ?>"><?= $feedback['state_title']; ?></span></dd>
                    <dt>Type</dt><dd><?= $feedback['type']; ?></dd>
                    <dt>Upvotes</dt><dd><?= $up; ?></dd>
                    <dt>Downvotes</dt><dd><?= $down; ?></dd>
                    <dt>Created</dt><dd><?= $feedback['date']->format('Y-m-d'); ?></dd>
                    <dt>Last activity</dt><dd><?= ($newComment !== null ? $newComment['date'] : $feedback['last_activity'])->format('Y-m-d'); ?></dd>
                </dl>
                <?php if ($up + $down > 0) : ?>
                    <div class="vote-ratio" title="<?= \round($up / ($up + $down) * 100); ?>% upvotes">
                        <span style="width: <?= \round($up / ($up + $down) * 100, 1); ?>%"></span>
                    </div>
                    <p class="muted chart-note"><?= \round($up / ($up + $down) * 100); ?>% upvotes</p>
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
