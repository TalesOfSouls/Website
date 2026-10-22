<?php
/** @var \phpOMS\Views\View $this */
$search  = $this->data['search'] ?? '';
$server  = $this->data['server'] ?? 0;
$servers = $this->data['servers'] ?? [];
$rows    = $this->data['rows'] ?? [];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Character list</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/player/character/profile">
        <input type="text" name="character" placeholder="Character" value="<?= $this->printHtml($search); ?>">
        <select name="server">
            <option value="">Any server</option>
            <?php foreach ($servers as $id => $name) : ?>
                <option value="<?= $id; ?>"<?= $id === $server ? ' selected' : ''; ?>><?= $this->printHtml($name); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Search</button>
    </form>

    <div class="floater">
        <table class="content-table encounter-table">
            <thead>
                <tr>
                    <th class="wf-100">Character</th>
                    <th>Level</th>
                    <th>Class</th>
                    <th>Server</th>
                    <th>Guild</th>
                    <th>Player</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td>
                            <a class="row-link" href="/player/character/profile/<?= $row['id']; ?>"><?= $this->printHtml($row['name']); ?></a>
                            <?php if ($row['dead']) : ?><span class="badge closed">Dead</span><?php endif; ?>
                        </td>
                        <td><?= $row['level']; ?></td>
                        <td><?= $this->printHtml($row['class']); ?></td>
                        <td class="nowrap"><?= $this->printHtml($row['server']); ?></td>
                        <td class="nowrap"><?= $row['guild'] > 0 ? $this->printHtml($row['guild_name']) : '-'; ?></td>
                        <td><?= $this->printHtml($row['player_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)) : ?>
                    <tr><td colspan="6">No characters found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
            $page    = $this->data['page'] ?? 1;
            $pages   = $this->data['pages'] ?? 1;
            $pageUrl = fn (int $p) : string => '/player/character/profile?' . \htmlspecialchars(\http_build_query(\array_filter(['character' => $search, 'server' => $server ?: null, 'page' => $p > 1 ? $p : null])));
            include __DIR__ . '/pager.tpl.php';
        ?>
    </div>
</div>
