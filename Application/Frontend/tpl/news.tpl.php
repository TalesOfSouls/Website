<?php
/** @var \phpOMS\Views\View $this */
$newsList = $this->data['news'] ?? [];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>News</h1>
    </div>
</div>
<div id="news-list">
    <?php foreach ($newsList as $news) : ?>
    <div class="news-section">
        <div class="floater">
            <div class="bg-img">
                <img src="<?= $this->printHtml($news['image']); ?>">
            </div>
            <div>
                <h5><?= $news['date']->format('Y-m-d'); ?></h5>
                <h3><?= $this->printHtml($news['title']); ?></h3>
                <p><?= $this->printHtml($news['excerpt']); ?></p>
            </div>
            <a class="stretched-link" href="/news/<?= $this->printHtml($news['slug']); ?>" aria-label="<?= $this->printHtml($news['title']); ?>"></a>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($newsList)) : ?>
    <div class="news-section">
        <div class="floater">
            <p>There are no news posts yet.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
