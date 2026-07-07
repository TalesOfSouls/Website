<div class="floater">
    <div id="welcome-section">
        <h1>Recordings</h1>
    </div>
</div>

<div id="ladder">
    <div class="floater">
        <input type="text" name="player" placeholder="Player(s)/Guild(s)">
        <input type="text" name="event" placeholder="Event">

        <input type="text" name="server" list="server_names" placeholder="Server">
        <datalist id="server_names">
            <option value="Boston">
            <option value="Cambridge">
        </datalist>

        <select>
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

        <select>
            <option disabled selected>Map
        </select>
    </div>

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
            </tbody>
        </table>
    </div>
</div>