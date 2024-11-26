<?php
use Models\PlayerClassMapper;

$classes = PlayerClassMapper::getAll()->executeGetArray();

$time = time();
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Ladder</h1>
    </div>
</div>

<div id="ladder">
    <div class="floater">
        <input type="text" name="player" placeholder="Player">

        <select>
            <option disabled selected>Time
            <option>All Time
            <option>Seasonal
        </select>

        <input type="text" name="server" list="server_names" placeholder="Server">
        <datalist id="server_names">
            <option value="Boston">
            <option value="Cambridge">
        </datalist>

        <select>
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

        <select>
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

        <select>
            <option disabled selected>Category
            <option>Player
            <option>Character
            <option>Guild
        </select>

        <select>
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
                <option>Total PvP
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
    </div>

    <div class="floater">
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
                <?php for ($i = 1; $i < 21; ++$i) : ?>
                <tr>
                    <td><?= $i; ?></td>
                    <td>Test Player</td>
                    <td>Class</td>
                    <td>Mode</td>
                    <td>Server</td>
                    <td>Level</td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>