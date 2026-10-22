<div class="floater">
    <div id="welcome-section">
        <h1>Submit feedback</h1>
    </div>
</div>

<div class="floater page-section">
    <?php if (!empty($this->data['sent'])) : ?>
        <div class="notice info">Thank you! Your feedback has been submitted and will appear on the board after review.</div>
    <?php endif; ?>
    <div class="panel">
        <p class="muted">Please search the <a href="/feedback">feedback board</a> first - if your idea already exists, vote for it instead.</p>
        <form class="site-form" method="POST" action="/feedback/create">
            <div class="form-group">
                <label for="feedback-type">Type</label>
                <select id="feedback-type" name="type" required>
                    <option>Suggestion</option>
                    <option>Bug</option>
                    <option>Balance</option>
                    <option>Website</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="feedback-title">Title</label>
                <input id="feedback-title" type="text" name="title" maxlength="120" required>
                <span class="form-hint">A short summary, e.g. "Guild bank permissions per rank".</span>
            </div>
            <div class="form-group">
                <label for="feedback-description">Description</label>
                <textarea id="feedback-description" name="description" required></textarea>
                <span class="form-hint">For bugs: what happened, what did you expect and how can we reproduce it?</span>
            </div>
            <button type="submit" class="featured">Submit</button>
            <a class="button" href="/feedback">Cancel</a>
        </form>
    </div>
</div>
