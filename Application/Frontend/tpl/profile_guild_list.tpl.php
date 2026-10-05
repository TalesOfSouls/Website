<?php
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
$search  = \trim($this->request->getDataString('guild') ?? '');
$samples = $this->data['sample'] ?? ['guilds' => []];

// Sample guilds first, followed by the guilds from the database
$rows = [];
foreach ($samples['guilds'] as $id => $guild) {
    $rows[] = ['id' => $id, 'name' => $guild['name'], 'server' => $guild['server'], 'members' => \count($guild['members'])];
}

foreach ($this->data['profiles'] as $profile) {
    if ($profile->id == 1) { continue; }
    $rows[] = ['id' => $profile->id, 'name' => $profile->name, 'server' => '-', 'members' => '-'];
}
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Guild list</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/guild/profile">
        <input type="text" name="guild" placeholder="Guild" value="<?= $this->printHtml($search); ?>">

        <input type="text" name="server" list="server_names" placeholder="Server">
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
                    <th class="wf-100">Guild</th>
                    <th>Server</th>
                    <th>Members</th>
                </tr>
            </thead>
            <tbody>
                <?php $c = 0; foreach ($rows as $row) :
                    if ($search !== '' && \stripos($row['name'], $search) === false) { continue; }
                    ++$c;
                    $url = UriFactory::build('{/base}/guild/profile/' . $row['id']);
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($row['name']); ?></a></td>
                        <td class="nowrap"><?= $this->printHtml($row['server']); ?></td>
                        <td><?= $row['members']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($c === 0) : ?>
                    <tr><td colspan="3">No guilds found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
