<?php
// @todo Sample town until towns are stored by the game servers
$buildings = [
    ['Town Hall', 3, 'Upgrade available'],
    ['Smithy', 2, 'Producing: Iron Ingot'],
    ['Farm', 4, 'Harvest in 2 h'],
    ['Tavern', 1, 'Open'],
    ['Watchtower', 2, 'Guarded'],
    ['Market', 1, 'Under construction'],
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Town Management</h1>
    </div>
</div>

<div class="floater page-section">
    <div class="notice">You need to <a href="/signin">sign in</a> to manage your town. The town below is an example.</div>

    <div class="stat-tiles">
        <div class="stat-tile"><span class="value">Rivenford</span><span class="label">Town</span></div>
        <div class="stat-tile"><span class="value">48</span><span class="label">Residents</span></div>
        <div class="stat-tile"><span class="value">3</span><span class="label">Town level</span></div>
        <div class="stat-tile"><span class="value">2,350</span><span class="label">Treasury (gold)</span></div>
    </div>

    <div class="layout-split right">
        <div class="panel">
            <h2>Buildings</h2>
            <table class="content-table">
                <thead><tr><th class="wf-100">Building</th><th>Level</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($buildings as $building) : ?>
                    <tr><td><?= $building[0]; ?></td><td><?= $building[1]; ?></td><td><?= $building[2]; ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div>
            <div class="panel">
                <h2>Resources</h2>
                <dl class="stat-list">
                    <dt>Wood</dt><dd>1,240</dd>
                    <dt>Stone</dt><dd>860</dd>
                    <dt>Iron</dt><dd>215</dd>
                    <dt>Food</dt><dd>3,100</dd>
                </dl>
            </div>
            <div class="panel">
                <h2>Housing</h2>
                <p>4 of 6 houses rented.</p>
                <div class="bar"><span style="width: 66%"></span></div>
                <p>Learn more about <a href="/wiki/town">towns</a>.</p>
            </div>
        </div>
    </div>
</div>
