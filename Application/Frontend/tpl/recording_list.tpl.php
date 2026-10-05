<div class="floater">
    <div id="welcome-section">
        <h1>Recordings</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/recordings">
        <input type="text" name="player" placeholder="Player(s)/Guild(s)">
        <input type="text" name="event" placeholder="Event">

        <input type="text" name="server" list="server_names" placeholder="Server">
        <datalist id="server_names">
            <option value="Test Server 1">
            <option value="Test Server 2">
        </datalist>

        <select name="type">
            <option disabled selected>Type
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
            <option>Raid
            <option>Dungeon
            <option>Race
        </select>

        <select name="map">
            <option disabled selected>Map
            <option>Any
        </select>

        <button type="submit">Filter</button>
    </form>

    <div class="floater">
        <table class="content-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Player(s)/Guild(s)</th>
                    <th>Event</th>
                    <th>Server</th>
                    <th>Type</th>
                    <th>Map</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // @todo Sample recordings until recordings are uploaded by the game servers
                $recordings = [
                    ['2026-09-30', 'Liquid', 'World first: Hall of Ashes', 'Test Server 1', 'Raid', 'Hall of Ashes'],
                    ['2026-09-29', 'Aldric vs. Thorgar', 'Arena Cup Final', 'Test Server 1', 'PvP Arena', 'Bloodsand Pit'],
                    ['2026-09-27', 'Method', 'Timed run +15', 'Test Server 2', 'Dungeon', 'Sunken Crypt'],
                    ['2026-09-25', 'Echo vs. Serenity', 'Siege of Ironhold', 'Test Server 2', 'PvP Siege', 'Ironhold'],
                    ['2026-09-22', 'Mirelle', 'Mount race record', 'Test Server 1', 'Race', 'Moonwood Circuit'],
                    ['2026-09-20', 'Neverland', 'Capture the Flag finals', 'Test Server 1', 'PvP Capture the Flag', 'Twin Towers'],
                ];
                foreach ($recordings as $recording) : ?>
                <tr>
                    <td><?= $recording[0]; ?></td>
                    <td><?= $recording[1]; ?></td>
                    <td><a href=""><?= $recording[2]; ?></a></td>
                    <td><?= $recording[3]; ?></td>
                    <td><?= $recording[4]; ?></td>
                    <td><?= $recording[5]; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>