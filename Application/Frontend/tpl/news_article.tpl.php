<?php
/** @var \phpOMS\Views\View $this */
$article = $this->data['article'];
$newer   = $this->data['newer'];
$older   = $this->data['older'];
?>
<div class="floater">
    <div id="welcome-section">
        <h1><?= $this->printHtml($article['title']); ?></h1>
        <p class="article-date"><?= $article['date']->format('F j, Y'); ?><?= $article['status'] !== 'published' ? ' &middot; <span class="badge">Draft</span>' : ($article['date'] > new \DateTime() ? ' &middot; <span class="badge planned">Scheduled</span>' : ''); ?></p>
    </div>
</div>

<div class="floater page-section">
    <?php if ($this->data['admin'] ?? false) : ?>
        <p class="admin-bar"><a class="button" href="/admin/news/<?= $article['id']; ?>">Edit post</a></p>
    <?php endif; ?>
    <div class="panel prose">
        <?= $this->data['content']; ?>
    </div>

    <div class="article-nav">
        <?php if ($older !== null) : ?>
            <a class="button" href="/news/<?= $this->printHtml($older['slug']); ?>">&larr; <?= $this->printHtml($older['title']); ?></a>
        <?php else : ?><span></span><?php endif; ?>
        <?php if ($newer !== null) : ?>
            <a class="button" href="/news/<?= $this->printHtml($newer['slug']); ?>"><?= $this->printHtml($newer['title']); ?> &rarr;</a>
        <?php endif; ?>
    </div>
</div>
