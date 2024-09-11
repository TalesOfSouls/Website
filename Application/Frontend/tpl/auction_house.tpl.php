<?php
use Models\ItemTypeMapper;

$types = ItemTypeMapper::getAll()->where('tradable', true)->executeGetArray();
?>

<div class="floater">
    <div id="welcome-section">
        <h1>Auction House</h1>
    </div>
</div>
<div id="auction" class="side-nav-content">
    <div class="floater">
        <div>
            <div class="side-nav main-content">
                <ul>
                <?php foreach ($types as $type) : ?>
                    <?php if ($type->releaseDate === null || $type->releaseDate->getTimestamp() < $time) : ?>
                        <li><a href="/auction/<?= \str_replace([' '], ['_'], \strtolower($type->name)); ?>"><?= $this->printHtml($type->name); ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div>
            <div class="main-content"><input type="text"></div>

            <div class="main-content">
                <table>
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
                        <tr>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>