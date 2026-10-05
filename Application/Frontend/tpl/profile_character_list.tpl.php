<?php
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
$search  = \trim($this->request->getDataString('character') ?? '');
$server  = \trim($this->request->getDataString('server') ?? '');
$samples = $this->data['sample'] ?? ['players' => [], 'characters' => []];

// Database characters + sample characters
$rows = [];
foreach ($this->data['profiles'] as $profile) {
    if ($profile->id == 1) { continue; }
    $rows[] = ['id' => $profile->id, 'name' => $profile->name, 'server' => '-', 'class' => '-', 'player' => null];
}

foreach ($samples['characters'] as $id => $character) {
    $rows[] = ['id' => $id, 'name' => $character['name'], 'server' => $character['server'], 'class' => $character['class'], 'player' => $character['player']];
}
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Character list</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/player/character/profile">
        <input type="text" name="character" placeholder="Character" value="<?= $this->printHtml($search); ?>">

        <input type="text" name="server" list="server_names" placeholder="Server" value="<?= $this->printHtml($server); ?>">
        <datalist id="server_names">
            <option value="Test Server 1">
            <option value="Test Server 2">
        </datalist>

        <button type="submit">Search</button>
    </form>

    <div class="floater">
        <table class="content-table">
            <thead>
                <tr>
                    <th class="wf-100">Character</th>
                    <th>Server</th>
                    <th>Class</th>
                    <th>Player</th>
                </tr>
            </thead>
            <tbody>
                <?php $c = 0; foreach ($rows as $row) :
                    if ($search !== '' && \stripos($row['name'], $search) === false) { continue; }
                    if ($server !== '' && \stripos($row['server'], $server) === false) { continue; }
                    ++$c;
                    $url    = UriFactory::build('{/base}/player/character/profile/' . $row['id']);
                    $player = $samples['players'][$row['player']] ?? null;
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($row['name']); ?></a></td>
                        <td class="nowrap"><?= $this->printHtml($row['server']); ?></td>
                        <td><?= $this->printHtml($row['class']); ?></td>
                        <td><?php if ($player !== null) : ?><a href="/player/profile/<?= $row['player']; ?>"><?= $this->printHtml($player['name']); ?></a><?php else : ?>-<?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($c === 0) : ?>
                    <tr><td colspan="4">No characters found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
