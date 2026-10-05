<?php
$markers = [
    ['Frostmere', 22, 18], ['Ironhold', 40, 30], ['Moonwood', 65, 25], ['The Styx', 50, 55],
    ['Ashen Wastes', 30, 70], ['Port Sorrow', 78, 62], ['Nomad Steppes', 60, 82], ['Fellmarch', 15, 48],
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>World Map</h1>
    </div>
</div>

<div class="floater page-section">
    <div class="notice">Region names and positions are placeholders.</div>
    <div class="layout-split right">
        <div class="map-frame">
            <img src="/Application/Frontend/img/mountain_side.jpg" alt="World map">
            <?php foreach ($markers as $marker) : ?>
                <span class="map-marker" style="left: <?= $marker[1]; ?>%; top: <?= $marker[2]; ?>%"><?= $marker[0]; ?></span>
            <?php endforeach; ?>
        </div>
        <div class="panel">
            <h2>Regions</h2>
            <ul class="side-menu">
                <?php foreach ($markers as $marker) : ?>
                    <li><a href="/wiki/location"><?= $marker[0]; ?></a></li>
                <?php endforeach; ?>
            </ul>
            <h3>Legend</h3>
            <ul class="list">
                <li>Cities &amp; villages</li>
                <li>Dungeons &amp; raids</li>
                <li>Resources</li>
                <li>World bosses</li>
            </ul>
        </div>
    </div>
</div>
