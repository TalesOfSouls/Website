<?php
use Application\Frontend\Provider\LadderData;
use Models\PlayerClassMapper;

/** @var \phpOMS\Views\View $this */
$classes = PlayerClassMapper::getAll()->executeGetArray();
$filter  = $this->data['filter'] ?? ['type' => 'Level', 'player' => '', 'server' => '', 'class' => '', 'mode' => '', 'seasonal' => false, 'category' => ''];
$ladder  = $this->data['ladder'] ?? null;

$time = time();

// keeps the selected filter values
$selected = function (string $name, string $value) use ($filter) : string {
    $current = match ($name) {
        'time'  => $filter['seasonal'] ? 'Seasonal' : '',
        default => $filter[$name] ?? '',
    };

    return $current === $value ? ' selected' : '';
};

$valueTitle = LadderData::TYPES[\str_starts_with($filter['type'], 'PvP') ? 'Any PvP' : $filter['type']] ?? 'Value';
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Ladder</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/ladder">
        <input type="text" name="player" placeholder="Player, character or guild" value="<?= $this->printHtml($filter['player']); ?>">

        <select name="time">
            <option disabled selected>Time
            <option>All Time
            <option<?= $selected('time', 'Seasonal'); ?>>Seasonal
        </select>

        <input type="text" name="server" list="server_names" placeholder="Server" value="<?= $this->printHtml($filter['server']); ?>">
        <datalist id="server_names">
            <?php foreach (\Models\ServerMapper::getAll()->executeGetArray() as $server) : ?>
                <option value="<?= $this->printHtml($server->name); ?>">
            <?php endforeach; ?>
        </datalist>

        <select name="mode">
            <option disabled selected>Mode
            <option<?= $selected('mode', 'Overall'); ?>>Overall
            <optgroup label="HC">
                <option<?= $selected('mode', 'Any HC'); ?>>Any HC
                <option<?= $selected('mode', 'HC'); ?>>HC
                <option<?= $selected('mode', 'HCSF'); ?>>HCSF
                <option<?= $selected('mode', 'HCS'); ?>>HCS
                <option<?= $selected('mode', 'HCSSF'); ?>>HCSSF
                <option<?= $selected('mode', 'HCGF'); ?>>HCGF
            </optgroup>
            <optgroup label="SC">
                <option<?= $selected('mode', 'Any SC'); ?>>Any SC
                <option<?= $selected('mode', 'SC'); ?>>SC
                <option<?= $selected('mode', 'SCSF'); ?>>SCSF
                <option<?= $selected('mode', 'SCS'); ?>>SCS
                <option<?= $selected('mode', 'SCSSF'); ?>>SCSSF
                <option<?= $selected('mode', 'SCGF'); ?>>SCGF
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
                        <option<?= $selected('class', $cl->name); ?>><?= $this->printHtml($cl->name); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
                </optgroup>
            <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <select name="category">
            <option disabled selected>Category
            <option<?= $selected('category', 'Player'); ?>>Player
            <option<?= $selected('category', 'Character'); ?>>Character
            <option<?= $selected('category', 'Guild'); ?>>Guild
        </select>

        <select name="type">
            <option disabled selected>Type
            <option<?= $selected('type', 'Level'); ?>>Level
            <option<?= $selected('type', 'Speedrun'); ?>>Speedrun
            <option<?= $selected('type', 'Achievement'); ?>>Achievement
            <option<?= $selected('type', 'Raid'); ?>>Raid
            <optgroup label="Dungeon">
                <option<?= $selected('type', 'Solo-NPC Dungeon'); ?>>Solo-NPC Dungeon
                <option<?= $selected('type', 'Grouped Dungeon'); ?>>Grouped Dungeon
            </optgroup>
            <optgroup label="Trial">
                <option<?= $selected('type', 'Solo Trial'); ?>>Solo Trial
                <option<?= $selected('type', 'Solo-NPC Trial'); ?>>Solo-NPC Trial
                <option<?= $selected('type', 'Grouped Trial'); ?>>Grouped Trial
            </optgroup>
            <option<?= $selected('type', 'World Dungeon'); ?>>World Dungeon
            <option<?= $selected('type', 'Trial'); ?>>Trial
            <option<?= $selected('type', 'Jumping Puzzle'); ?>>Jumping Puzzle
            <option<?= $selected('type', 'Holdout'); ?>>Holdout
            <option<?= $selected('type', 'Dodging'); ?>>Dodging
            <optgroup label="PvP">
                <option<?= $selected('type', 'Any PvP'); ?>>Any PvP
                <option<?= $selected('type', 'PvP Arena'); ?>>PvP Arena
                <option<?= $selected('type', 'PvP Pet Battles'); ?>>PvP Pet Battles
                <option<?= $selected('type', 'PvP Capture the Flag'); ?>>PvP Capture the Flag
                <option<?= $selected('type', 'PvP World vs World'); ?>>PvP World vs World
                <option<?= $selected('type', 'PvP Siege'); ?>>PvP Siege
                <option<?= $selected('type', 'PvP Team Deathmatch'); ?>>PvP Team Deathmatch
                <option<?= $selected('type', 'PvP Capture the Objective(s)'); ?>>PvP Capture the Objective(s)
                <option<?= $selected('type', 'PvP Escort'); ?>>PvP Escort
                <option<?= $selected('type', 'PvP Moba'); ?>>PvP Moba
                <option<?= $selected('type', 'PvP Battle Royal'); ?>>PvP Battle Royal
            </optgroup>
            <optgroup label="Stats">
                <option<?= $selected('type', 'DPS'); ?>>DPS
                <option<?= $selected('type', 'Health'); ?>>Health
                <option<?= $selected('type', 'Healing'); ?>>Healing
            </optgroup>
        </select>

        <button type="submit">Filter</button>
    </form>

    <div class="floater">
        <?php if ($ladder === null) : ?>
            <div class="notice info">The <?= $this->printHtml($filter['type']); ?> ladder is not tracked yet. Tracked ladders: <?= \implode(', ', \array_keys(LadderData::TYPES)); ?>.</div>
        <?php else : ?>
        <table class="content-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th class="wf-100"><?= \in_array($filter['type'], ['Achievement'], true) ? 'Player' : ($filter['type'] === 'Raid' ? 'Guild' : 'Character'); ?></th>
                    <th>Class</th>
                    <th>Mode</th>
                    <th>Server</th>
                    <th><?= $valueTitle; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ladder as $i => $row) : ?>
                <tr>
                    <td><?= $i + 1; ?></td>
                    <td><a href="<?= $row['link']; ?>"><?= $this->printHtml($row['name']); ?></a></td>
                    <td><?= $this->printHtml($row['class']); ?></td>
                    <td><?= $row['mode']; ?></td>
                    <td class="nowrap"><?= $this->printHtml($row['server']); ?></td>
                    <td><?= $row['value']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($ladder)) : ?>
                    <tr><td colspan="6">No entries found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>