<?php
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */

/** @var \Models\Faq $faq */
$faq        = $this->data['faq'];
$categories = $this->data['categories'] ?? [];
$csrf       = UriFactory::getQuery('$CSRF') ?? '';
$isNew      = $faq->id === 0;

include __DIR__ . '/nav.tpl.php';
?>
    <div class="layout-split right">
        <div class="panel">
            <form id="admin-faq-form" class="site-form wide" method="POST" action="/api/admin/faq">
                <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                <input type="hidden" name="id" value="<?= $faq->id; ?>">
                <input type="hidden" name="redirect" value="/admin/faq<?= $isNew ? '' : '/' . $faq->id; ?>">
                <div class="form-group">
                    <label for="faq-question">Question</label>
                    <input id="faq-question" type="text" name="question" minlength="5" maxlength="300" value="<?= $this->printHtml($faq->question); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="faq-category">Category</label>
                        <input id="faq-category" type="text" name="category" list="faq-categories" maxlength="50" value="<?= $this->printHtml($faq->category); ?>" required>
                        <datalist id="faq-categories">
                            <?php foreach ($categories as $category) : ?><option value="<?= $this->printHtml($category); ?>"><?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label for="faq-order">Order</label>
                        <input id="faq-order" type="number" name="order" value="<?= $faq->order; ?>">
                        <span class="form-hint">Lower numbers are shown first.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="faq-answer">Answer</label>
                    <textarea id="faq-answer" name="answer" class="editor" required><?= $this->printHtml($faq->answer); ?></textarea>
                    <span class="form-hint">Markdown.</span>
                </div>
                <input type="hidden" name="milestone" value="<?= $this->printHtml($faq->milestone !== '' ? $faq->milestone : 'Development'); ?>">
                <button type="submit" class="featured"><?= $isNew ? 'Create question' : 'Save'; ?></button>
                <a class="button" href="/admin/faq">Back</a>
            </form>
        </div>
        <div class="panel">
            <h2>Details</h2>
            <dl class="stat-list">
                <dt>Created</dt><dd><?= $isNew ? '-' : $faq->datetime->format('Y-m-d'); ?></dd>
            </dl>
            <?php if (!$isNew) : ?>
                <form id="admin-faq-delete" class="inline-form" method="POST" action="/api/admin/faq/delete" data-confirm="Delete this question?">
                    <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                    <input type="hidden" name="id" value="<?= $faq->id; ?>">
                    <button type="submit" class="danger">Delete question</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
