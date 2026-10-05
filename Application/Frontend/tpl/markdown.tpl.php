<?php
/** @var \phpOMS\Views\View $this */
?>
<div class="floater page-section">
    <div class="panel prose">
        <?php if (empty($this->data['markdown'])) : ?>
            <p class="muted">This page will be available soon.</p>
        <?php else : ?>
            <?= $this->data['markdown']; ?>
        <?php endif; ?>
    </div>
</div>
