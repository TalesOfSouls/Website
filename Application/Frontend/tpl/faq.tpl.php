<?php
use phpOMS\Utils\Parser\Markdown\Markdown;

/** @var \phpOMS\Views\View $this */
$faqs       = $this->data['faq'] ?? [];
$categories = $this->data['categories'] ?? [];
$current    = $this->data['category'] ?? '';
$search     = $this->data['search'] ?? '';
?>
<div class="floater">
    <div id="welcome-section">
        <h1>FAQ</h1>
    </div>
</div>

<div id="faq-list">
    <form class="floater filter-bar" method="GET" action="/faq">
        <select name="category">
            <option value=""<?= $current === '' ? ' selected' : ''; ?>>Any category
            <?php foreach ($categories as $category) : ?>
            <option value="<?= $this->printHtml($category); ?>"<?= $current === $category ? ' selected' : ''; ?>><?= $this->printHtml($category); ?>
            <?php endforeach; ?>
        </select>

        <input type="text" name="q" placeholder="FAQ" value="<?= $this->printHtml($search); ?>">

        <button type="submit">Search</button>
        <?php if ($this->data['admin'] ?? false) : ?>
            <a class="button" href="/admin/faq">Manage FAQ</a>
        <?php endif; ?>
    </form>

    <?php foreach ($faqs as $faq) : ?>
    <div class="faq-section">
        <div class="floater">
            <details class="faq-details">
                <summary><h3><?= $this->printHtml($faq->question); ?></h3></summary>
                <article>
                    <?= Markdown::parse($faq->answer); ?>
                    <p class="faq-date"><?= $this->printHtml($faq->category); ?> &middot; <?= $faq->datetime->format('Y-m-d'); ?><?php if ($this->data['admin'] ?? false) : ?> &middot; <a href="/admin/faq/<?= $faq->id; ?>">Edit</a><?php endif; ?></p>
                </article>
            </details>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($faqs)) : ?>
    <div class="faq-section">
        <div class="floater">
            <p>No questions match your search.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
