<?php
// Link sample names to the sample character profiles
$sampleCharacters = [];
foreach ((include __DIR__ . '/../data/sample_profiles.php')['characters'] as $sampleId => $sampleCharacter) {
    $sampleCharacters[$sampleCharacter['name']] = $sampleId;
}
$characterUrl = fn (string $name) : string => '/player/character/profile' . (isset($sampleCharacters[$name]) ? '/' . $sampleCharacters[$name] : '');

use Models\PlayerClassMapper;

$classes = PlayerClassMapper::getAll()->executeGetArray();

$time = time();
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Ladder</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/ladder">
        <input type="text" name="player" placeholder="Player">

        <select name="time">
            <option disabled selected>Time
            <option>All Time
            <option>Seasonal
        </select>

        <input type="text" name="server" list="server_names" placeholder="Server">
        <datalist id="server_names">
            <option value="Test Server 1">
            <option value="Test Server 2">
        </datalist>

        <select name="mode">
            <option disabled selected>Mode
            <option>Overall
            <optgroup label="HC">
                <option>Any HC
                <option>HC
                <option>HCSF
                <option>HCS
                <option>HCSSF
                <option>HCGF
            </optgroup>
            <optgroup label="SC">
                <option>Any SC
                <option>SC
                <option>SCSF
                <option>SCS
                <option>SCSSF
                <option>SCGF
            </optgroup>
        </select>

        <select name="class">
            <option disabled selected>Class
            <option>Any
            <?php foreach ($classes as $category) : ?>
            <?php if ($category->parent === null
                && ($category->releaseDate === null || $category->releaseDate->getTimestamp() < $time)) : ?>
                <optgroup label="<?= $this->printHtml($category->name); ?>">
                <?php foreach ($classes as $cl) : ?>
                    <?php if ($cl->parent === $category->id
                        && ($cl->releaseDate === null || $cl->releaseDate->getTimestamp() < $time)) : ?>
                        <option><?= $this->printHtml($cl->name); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
                </optgroup>
            <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <select name="category">
            <option disabled selected>Category
            <option>Player
            <option>Character
            <option>Guild
        </select>

        <select name="type">
            <option disabled selected>Type
            <option>Level
            <option>Speedrun
            <option>Achievement
            <option>Raid
            <optgroup label="Dungeon">
                <option>Solo-NPC Dungeon
                <option>Grouped Dungeon
            </optgroup>
            <optgroup label="Trial">
                <option>Solo Trial
                <option>Solo-NPC Trial
                <option>Grouped Trial
            </optgroup>
            <option>World Dungeon
            <option>Trial
            <option>Jumping Puzzle
            <option>Holdout
            <option>Dodging
            <optgroup label="PvP">
                <option>Any PvP
                <option>PvP Arena
                <option>PvP Pet Battles
                <option>PvP Capture the Flag
                <option>PvP World vs World
                <option>PvP Siege
                <option>PvP Team Deathmatch
                <option>PvP Capture the Objective(s)
                <option>PvP Escort
                <option>PvP Moba
                <option>PvP Battle Royal
            </optgroup>
            <optgroup label="Stats">
                <option>DPS
                <option>Health
                <option>Healing
            </optgroup>
        </select>

        <button type="submit">Filter</button>
    </form>

    <div class="floater">
        <div class="notice info">Season 0 (preview) &middot; The ladder shows sample data until the first beta starts.</div>
        <table class="content-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player/Guild</th>
                    <th>Class</th>
                    <th>Mode</th>
                    <th>Server</th>
                    <th>Level</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $samplePlayers = ['Aldric', 'Mirelle', 'Thorgar', 'Sylvara', 'Kaelen', 'Brannoc', 'Ysolde', 'Corvin', 'Elowen', 'Draven',
                    'Isolde', 'Fenrik', 'Lyra', 'Garrick', 'Nimue', 'Torvald', 'Seraphine', 'Rurik', 'Maelis', 'Oswin',
                    'Vespera', 'Hadrian', 'Talia', 'Ulric', 'Wren'];
                $sampleClasses = ['Paladin', 'Mage', 'Berserker', 'Druid', 'Assassin', 'Necromancer', 'Priest', 'Hunter', 'Knight', 'Shaman'];
                $sampleModes   = ['SC', 'HC', 'SCSF', 'HCSSF', 'SCGF'];
                foreach ($samplePlayers as $i => $player) : ?>
                <tr>
                    <td><?= $i + 1; ?></td>
                    <td><a href="<?= $characterUrl($player); ?>"><?= $player; ?></a></td>
                    <td><?= $sampleClasses[$i % \count($sampleClasses)]; ?></td>
                    <td><?= $sampleModes[$i % \count($sampleModes)]; ?></td>
                    <td>Test Server <?= $i % 2 + 1; ?></td>
                    <td><?= \max(1, 99 - \intdiv($i * 3, 2)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>