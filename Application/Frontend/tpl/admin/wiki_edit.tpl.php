<?php
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */

/** @var \Models\WikiPage $page */
$page    = $this->data['page'];
$modules = $this->data['modules'] ?? [];
$csrf    = UriFactory::getQuery('$CSRF') ?? '';
$isNew   = $page->id === 0;

include __DIR__ . '/nav.tpl.php';
?>
    <div class="layout-split right">
        <div class="panel">
            <form id="admin-wiki-form" class="site-form wide" method="POST" action="/api/admin/wiki">
                <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                <input type="hidden" name="id" value="<?= $page->id; ?>">
                <input type="hidden" name="redirect" value="/admin/wiki<?= $isNew ? '' : '/' . $page->id; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="wiki-title">Title</label>
                        <input id="wiki-title" type="text" name="title" maxlength="200" value="<?= $this->printHtml($page->title); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="wiki-slug">Url</label>
                        <input id="wiki-slug" type="text" name="slug" maxlength="120" pattern="[a-z0-9_\-]+(/[a-z0-9_\-]+)*" value="<?= $this->printHtml($page->slug); ?>" required<?= $page->slug === 'index' ? ' readonly' : ''; ?>>
                        <span class="form-hint">/wiki/<strong>url</strong> (a-z, 0-9, -, _ and /)</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="wiki-nav-title">Navigation title</label>
                        <input id="wiki-nav-title" type="text" name="nav_title" maxlength="100" value="<?= $this->printHtml($page->navTitle); ?>" placeholder="same as the title">
                    </div>
                    <div class="form-group">
                        <label for="wiki-nav-order">Navigation position</label>
                        <input id="wiki-nav-order" type="number" name="nav_order" min="0" value="<?= $page->navOrder ?? ''; ?>">
                        <span class="form-hint">Empty = not in the navigation.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="wiki-module">Data block</label>
                    <select id="wiki-module" name="module">
                        <option value="">None</option>
                        <?php foreach ($modules as $module) : ?>
                            <option value="<?= $module; ?>"<?= $page->module === $module ? ' selected' : ''; ?>><?= \ucfirst($module); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Data from the game database shown below the article (e.g. all classes or races).</span>
                </div>
                <div class="form-group">
                    <label for="wiki-content">Content</label>
                    <textarea id="wiki-content" name="content" class="editor"><?= $this->printHtml($page->content); ?></textarea>
                    <span class="form-hint">Markdown (html is allowed). The title is added automatically.</span>
                </div>
                <button type="submit" class="featured"><?= $isNew ? 'Create article' : 'Save'; ?></button>
                <a class="button" href="/admin/wiki">Back</a>
                <?php if (!$isNew) : ?>
                    <a class="button" href="/wiki<?= $page->slug === 'index' ? '' : '/' . $this->printHtml($page->slug); ?>" target="_blank" rel="noopener">View</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="panel">
            <h2>Details</h2>
            <dl class="stat-list">
                <dt>Last change</dt><dd><?= $isNew ? '-' : $page->updated->format('Y-m-d H:i'); ?></dd>
            </dl>
            <?php if (!$isNew && $page->slug !== 'index') : ?>
                <form id="admin-wiki-delete" class="inline-form" method="POST" action="/api/admin/wiki/delete" data-confirm="Delete this article?">
                    <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                    <input type="hidden" name="id" value="<?= $page->id; ?>">
                    <button type="submit" class="danger">Delete article</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
