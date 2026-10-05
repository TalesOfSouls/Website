<?php
/** @var \phpOMS\Views\View $this */
$slug = $this->data['item'] ?? '';
$name = \ucwords(\str_replace('-', ' ', $slug));
?>
<div class="floater">
    <div id="welcome-section">
        <p class="article-meta"><a class="text-link" href="/shop">Shop</a></p>
        <h1><?= $this->printHtml($name); ?></h1>
    </div>
</div>

<div class="floater page-section">
    <div class="notice">The shop is not open yet.</div>
    <div class="layout-split right">
        <div class="panel">
            <div class="bg-img" style="height: 300px; overflow: hidden;">
                <img src="/Application/Frontend/img/dusk_2.jpg" alt="">
            </div>
            <h2>Description</h2>
            <p>A detailed description, screenshots and the full list of contents will be shown here.</p>
        </div>
        <div class="panel">
            <h2>Purchase</h2>
            <dl class="stat-list">
                <dt>Price</dt><dd>TBA</dd>
                <dt>Platform</dt><dd>PC</dd>
                <dt>Delivery</dt><dd>Digital</dd>
            </dl>
            <p><a class="button featured" href="/shop/buy/oneclick?item=<?= \urlencode($slug); ?>">Buy now</a></p>
            <p class="muted">You need to be signed in to buy.</p>
        </div>
    </div>
</div>
