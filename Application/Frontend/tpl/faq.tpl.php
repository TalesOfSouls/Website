<?php
use phpOMS\Utils\Parser\Markdown\Markdown;
?>
<div class="floater">
    <div id="welcome-section">
        <h1>FAQ</h1>
    </div>
</div>

<div id="faq-list">
    <?php foreach ($this->data['faq'] as $faq) : ?>
    <div class="faq-section">
        <div class="floater">
            <article>
                <h5><?= $faq->datetime->format('Y-m-d'); ?></h5>
                <h3><?= $this->printHtml($faq->question); ?></h3>
                <p><?= Markdown::parse($faq->answer); ?></p>
            </article>
        </div>
    </div>
    <?php endforeach; ?>
</div>