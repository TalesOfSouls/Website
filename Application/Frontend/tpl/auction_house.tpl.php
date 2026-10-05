<?php
use Models\ItemTypeMapper;

/** @var \phpOMS\Views\View $this */
$types    = ItemTypeMapper::getAll()->where('tradable', true)->executeGetArray();
$current  = $this->data['category'] ?? '';
$time     = \time();

// @todo Replace with the auction table once the game server writes auctions
$auctions = [
    ['Iron Ingot', 'White', 12, 5, 0, 15],
    ['Oak Wood', 'White', 3, 200, 2, 4],
    ['Pumpkin Soup', 'White', 8, 20, 1, 10],
    ['Steel Longsword', 'Blue', 450, 1, 3, 600],
    ['Silk Fabric', 'Blue', 35, 40, 0, 45],
    ['Moonleaf', 'Yellow', 120, 12, 5, 150],
    ['Runed Helmet', 'Yellow', 1200, 1, 2, 1500],
    ['Dragonscale Boots', 'Orange', 9800, 1, 7, 12000],
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Auction House</h1>
    </div>
</div>
<div id="auction" class="page-section">
    <div class="floater">
        <div class="layout-split">
            <div class="panel">
                <h2>Categories</h2>
                <ul class="side-menu">
                    <li><a href="/auction"<?= $current === '' ? ' class="active"' : ''; ?>>All</a></li>
                <?php foreach ($types as $type) :
                    $slug = \str_replace([' '], ['_'], \strtolower($type->name));
                ?>
                    <?php if ($type->releaseDate === null || $type->releaseDate->getTimestamp() < $time) : ?>
                        <li><a href="/auction/<?= $slug; ?>"<?= $current === $slug ? ' class="active"' : ''; ?>><?= $this->printHtml($type->name); ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <form class="filter-bar" method="GET" action="/auction<?= $current !== '' ? '/' . $this->printHtml($current) : ''; ?>">
                    <input type="text" name="q" placeholder="Item name">
                    <input type="text" name="server" list="server_names" placeholder="Server">
                    <datalist id="server_names">
                        <option value="Test Server 1">
                        <option value="Test Server 2">
                    </datalist>
                    <select name="rarity">
                        <option value="">Any rarity</option>
                        <option class="rarity-white">White</option>
                        <option class="rarity-blue">Blue</option>
                        <option class="rarity-yellow">Yellow</option>
                        <option class="rarity-orange">Orange</option>
                    </select>
                    <select name="sort">
                        <option value="price">Price</option>
                        <option value="ending">Ending soon</option>
                        <option value="newest">Newest</option>
                    </select>
                    <button type="submit">Search</button>
                </form>

                <div class="notice info">Sample offers - live auctions will appear here once the game servers are online.</div>

                <table class="content-table">
                    <thead>
                        <tr>
                            <th class="wf-100">Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Bids</th>
                            <th>Instant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auctions as $auction) : ?>
                        <tr>
                            <td class="rarity-<?= \strtolower($auction[1]); ?>"><?= $auction[0]; ?></td>
                            <td><?= \number_format($auction[2]); ?></td>
                            <td><?= $auction[3]; ?></td>
                            <td><?= $auction[4]; ?></td>
                            <td><?= \number_format($auction[5]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
