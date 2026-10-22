<?php
// @todo Pets aren't stored yet, the character names are linked to the characters with the same name
$characterUrl = function (string $name) : string {
    static $ids = [];

    $ids[$name] ??= \Models\CharacterMapper::get()->where('name', $name)->execute()->id;

    return '/player/character/profile' . ($ids[$name] > 0 ? '/' . $ids[$name] : '');
};

// @todo Sample pets until pets are stored by the game servers
$pets = [
    ['Ember', 'Fire Salamander', 'Rare', 42, 'Aldric', 18, 4],
    ['Shadow', 'Night Panther', 'Epic', 57, 'Mirelle', 31, 7],
    ['Pebble', 'Rock Turtle', 'Common', 12, 'Thorgar', 3, 9],
    ['Frost', 'Snow Owl', 'Rare', 38, 'Sylvara', 12, 5],
    ['Biscuit', 'Farm Dog', 'Common', 25, 'Corvin', 7, 7],
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Pet Profiles</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/pet/profile">
        <input type="text" name="pet" placeholder="Pet name">
        <input type="text" name="owner" placeholder="Owner">
        <select name="species">
            <option value="">Any species</option>
            <option>Salamander</option>
            <option>Panther</option>
            <option>Turtle</option>
            <option>Owl</option>
            <option>Dog</option>
            <option>Cat</option>
        </select>
        <button type="submit">Search</button>
    </form>

    <div class="floater">
        <div class="notice info">Sample pets - your own pets will appear here once you have tamed them in game.</div>
        <table class="content-table">
            <thead>
                <tr>
                    <th class="wf-100">Pet</th>
                    <th>Species</th>
                    <th>Quality</th>
                    <th>Level</th>
                    <th>Owner</th>
                    <th>Wins</th>
                    <th>Losses</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pets as $pet) : ?>
                <tr>
                    <td><?= $pet[0]; ?></td>
                    <td><?= $pet[1]; ?></td>
                    <td><?= $pet[2]; ?></td>
                    <td><?= $pet[3]; ?></td>
                    <td><a href="<?= $characterUrl($pet[4]); ?>"><?= $pet[4]; ?></a></td>
                    <td><?= $pet[5]; ?></td>
                    <td><?= $pet[6]; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
