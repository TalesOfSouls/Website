<?php
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */

/** @var \Models\News $news */
$news   = $this->data['news'];
$images = $this->data['images'] ?? [];
$csrf   = UriFactory::getQuery('$CSRF') ?? '';
$isNew  = $news->id === 0;

include __DIR__ . '/nav.tpl.php';
?>
    <div class="layout-split right">
        <div class="panel">
            <form id="admin-news-form" class="site-form wide" method="POST" action="/api/admin/news">
                <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                <input type="hidden" name="id" value="<?= $news->id; ?>">
                <input type="hidden" name="redirect" value="/admin/news<?= $isNew ? '' : '/' . $news->id; ?>">
                <div class="form-group">
                    <label for="news-title">Title</label>
                    <input id="news-title" type="text" name="title" minlength="3" maxlength="200" value="<?= $this->printHtml($news->title); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="news-slug">Url</label>
                        <input id="news-slug" type="text" name="slug" maxlength="120" pattern="[a-z0-9\-]*" value="<?= $this->printHtml($news->slug); ?>" placeholder="created from the title">
                        <span class="form-hint">/news/<strong><?= $this->printHtml($news->slug !== '' ? $news->slug : 'yyyy-mm-dd-title'); ?></strong></span>
                    </div>
                    <div class="form-group">
                        <label for="news-published">Publish date</label>
                        <input id="news-published" type="datetime-local" name="published" value="<?= $isNew ? '' : $news->published->format('Y-m-d\TH:i'); ?>">
                        <span class="form-hint">Empty = now. Posts with a future date are shown from that date on.</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="news-status">Status</label>
                        <select id="news-status" name="status">
                            <option value="draft"<?= $news->status === 'draft' ? ' selected' : ''; ?>>Draft (only visible for administrators)</option>
                            <option value="published"<?= $news->status === 'published' ? ' selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="news-image">Image</label>
                        <input id="news-image" type="text" name="image" list="news-images" value="<?= $this->printHtml($news->image); ?>" placeholder="/Application/Frontend/img/hills.jpg">
                        <datalist id="news-images">
                            <?php foreach ($images as $image) : ?><option value="<?= $this->printHtml($image); ?>"><?php endforeach; ?>
                        </datalist>
                        <span class="form-hint">Shown in the news lists. Empty = default image.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="news-summary">Summary</label>
                    <textarea id="news-summary" name="summary" class="short" maxlength="300"><?= $this->printHtml($news->summary); ?></textarea>
                    <span class="form-hint">Short text for the news lists. Empty = first paragraph of the post.</span>
                </div>
                <div class="form-group">
                    <label for="news-content">Content</label>
                    <textarea id="news-content" name="content" class="editor" required><?= $this->printHtml($news->content); ?></textarea>
                    <span class="form-hint">Markdown, e.g. <code>## Heading</code>, <code>**bold**</code>, <code>[link](/wiki)</code>, <code>![image](/Application/Frontend/img/sky.jpg)</code>.</span>
                </div>
                <button type="submit" class="featured"><?= $isNew ? 'Create post' : 'Save'; ?></button>
                <a class="button" href="/admin/news">Back</a>
                <?php if (!$isNew) : ?>
                    <a class="button" href="/news/<?= $this->printHtml($news->slug); ?>" target="_blank" rel="noopener">View</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="panel">
            <h2>Details</h2>
            <dl class="stat-list">
                <dt>Author</dt><dd><?= $isNew ? '-' : $this->printHtml($news->account->name); ?></dd>
                <dt>Created</dt><dd><?= $isNew ? '-' : $news->created->format('Y-m-d'); ?></dd>
                <dt>Updated</dt><dd><?= $news->updated?->format('Y-m-d H:i') ?? '-'; ?></dd>
            </dl>
            <?php if (!$isNew) : ?>
                <form id="admin-news-delete" class="inline-form" method="POST" action="/api/admin/news/delete" data-confirm="Delete this news post?">
                    <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                    <input type="hidden" name="id" value="<?= $news->id; ?>">
                    <button type="submit" class="danger">Delete post</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
