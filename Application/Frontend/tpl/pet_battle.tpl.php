<?php
// Link sample names to the sample character profiles
$sampleCharacters = [];
foreach ((include __DIR__ . '/../data/sample_profiles.php')['characters'] as $sampleId => $sampleCharacter) {
    $sampleCharacters[$sampleCharacter['name']] = $sampleId;
}
$characterUrl = fn (string $name) : string => '/player/character/profile' . (isset($sampleCharacters[$name]) ? '/' . $sampleCharacters[$name] : '');

// @todo Sample data until pet battles are recorded
$ranking = [
    ['Mirelle', 'Shadow', 1840, 31, 7],
    ['Aldric', 'Ember', 1712, 18, 4],
    ['Sylvara', 'Frost', 1655, 12, 5],
    ['Corvin', 'Biscuit', 1502, 7, 7],
    ['Thorgar', 'Pebble', 1390, 3, 9],
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Pet Battles</h1>
    </div>
</div>

<div class="floater page-section">
    <div class="layout-split right">
        <div class="panel">
            <h2>Top pet trainers</h2>
            <table class="content-table">
                <thead><tr><th>#</th><th class="wf-100">Trainer</th><th>Best pet</th><th>Rating</th><th>W</th><th>L</th></tr></thead>
                <tbody>
                <?php foreach ($ranking as $i => $row) : ?>
                    <tr>
                        <td><?= $i + 1; ?></td>
                        <td><a href="<?= $characterUrl($row[0]); ?>"><?= $row[0]; ?></a></td>
                        <td><a href="/pet/profile"><?= $row[1]; ?></a></td>
                        <td><?= $row[2]; ?></td>
                        <td><?= $row[3]; ?></td>
                        <td><?= $row[4]; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><a class="button small" href="/ladder?type=PvP+Pet+Battles">Full ladder</a></p>
        </div>
        <div class="panel">
            <h2>How it works</h2>
            <ul class="list">
                <li>Teams of up to three pets</li>
                <li>Each pet has four abilities and one passive trait</li>
                <li>Elemental strengths and weaknesses decide many fights</li>
                <li>Ranked seasons with exclusive rewards</li>
            </ul>
            <p>Learn more about <a href="/wiki/pet">pets</a> in the wiki.</p>
        </div>
    </div>
</div>
