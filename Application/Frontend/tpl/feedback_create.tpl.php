<?php
use Application\Frontend\Provider\FeedbackData;
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
$signedIn = ($this->data['account'] ?? 0) > 0;
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Submit feedback</h1>
    </div>
</div>

<div class="floater page-section">
    <div class="panel">
        <?php if (!$signedIn) : ?>
            <p>You need an account to submit feedback.</p>
            <p><a class="button featured" href="/signin?redirect=%2Ffeedback%2Fcreate">Sign in</a> <a class="button" href="/signup">Create account</a></p>
        <?php else : ?>
        <p class="muted">Please search the <a href="/feedback">feedback board</a> first - if your idea already exists, vote for it instead.</p>
        <form id="feedback-create" class="site-form" method="POST" action="/api/feedback">
            <input type="hidden" name="CSRF" value="<?= UriFactory::getQuery('$CSRF'); ?>">
            <div class="form-group">
                <label for="feedback-type">Type</label>
                <select id="feedback-type" name="type" required>
                    <?php foreach (FeedbackData::TYPES as $type) : ?>
                        <option><?= $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="feedback-title">Title</label>
                <input id="feedback-title" type="text" name="title" minlength="5" maxlength="120" required>
                <span class="form-hint">A short summary, e.g. "Guild bank permissions per rank".</span>
            </div>
            <div class="form-group">
                <label for="feedback-description">Description</label>
                <textarea id="feedback-description" name="description" minlength="20" maxlength="5000" required></textarea>
                <span class="form-hint">For bugs: what happened, what did you expect and how can we reproduce it?</span>
            </div>
            <button type="submit" class="featured">Submit</button>
            <a class="button" href="/feedback">Cancel</a>
        </form>
        <?php endif; ?>
    </div>
</div>
