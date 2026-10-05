<?php
// @todo Load the shop items from the database once the shop backend exists
$editions = [
    ['standard', 'Standard Edition', '29.99', 'The full game. No subscription, no pay-to-win.', '/Application/Frontend/img/hills.jpg'],
    ['deluxe', 'Deluxe Edition', '49.99', 'The full game plus digital artbook, soundtrack and an exclusive mount.', '/Application/Frontend/img/sky.jpg'],
    ['collector', 'Collector\'s Edition', '129.99', 'Everything from Deluxe plus the printed game documentation and a figurine.', '/Application/Frontend/img/dusk.jpg'],
];
$cosmetics = [
    ['ui-theme-ember', 'UI Theme: Ember', '4.99'],
    ['ui-theme-frost', 'UI Theme: Frost', '4.99'],
    ['pet-skin-spectral', 'Spectral Pet Skin', '6.99'],
    ['housing-pack-elven', 'Elven Housing Pack', '9.99'],
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Shop</h1>
    </div>
</div>

<div class="floater page-section">
    <div class="notice">The shop is not open yet - editions, contents and prices are preliminary.</div>

    <h2>Editions</h2>
    <div class="card-grid">
        <?php foreach ($editions as $edition) : ?>
        <a class="card" href="/shop/item/<?= $edition[0]; ?>">
            <div class="bg-img"><img src="<?= $edition[4]; ?>" alt=""></div>
            <div class="card-body">
                <h3><?= $edition[1]; ?></h3>
                <p><?= $edition[3]; ?></p>
            </div>
            <div class="card-footer">&euro;<?= $edition[2]; ?></div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="floater page-section">
    <h2>Cosmetics</h2>
    <div class="card-grid">
        <?php foreach ($cosmetics as $cosmetic) : ?>
        <a class="card" href="/shop/item/<?= $cosmetic[0]; ?>">
            <div class="card-body">
                <h3><?= $cosmetic[1]; ?></h3>
            </div>
            <div class="card-footer">&euro;<?= $cosmetic[2]; ?></div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
