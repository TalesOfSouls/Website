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
                <option>HCS
                <option>HCSF
                <option>HCSSF
            </optgroup>
            <optgroup label="SC">
                <option>Any SC
                <option>SC
                <option>SCS
                <option>SCSF
                <option>SCSSF
            </optgroup>
        </select>

        <select>
            <option disabled selected>Class
            <option>Overall
            <optgroup label="Warrior">
                <option>Monk
                <option>Paladin
                <option>Knight
                <option>Dualist
            </optgroup>
            <optgroup label="Outcast">
                <option>Berserker
                <option>Assassin
                <option>Engineer
                <option>Archeologist
                <option>Tormentor
            </optgroup>
            <optgroup label="Ranger">
                <option>Scout
                <option>Hunter
                <option>Beast Master
                <option>Druid
            </optgroup>
            <optgroup label="Caster">
                <option>Mage
                <option>Priest
                <option>Totemist
                <option>Shaman
                <option>Spellblade
                <option>Scribe
                <option>Bard
            </optgroup>
            <optgroup label="Summoner">
                <option>Necromancer
                <option>Witch Doctor
                <option>Conjuror
            </optgroup>
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
            <option>Achievement
            <option>Raid
            <option>Dungeon
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
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
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