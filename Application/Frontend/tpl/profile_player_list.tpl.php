<?php
/** @var \phpOMS\Views\View $this */
$search = $this->data['search'] ?? '';
$rows   = $this->data['rows'] ?? [];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Player list</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/player/profile">
        <input type="text" name="player" placeholder="Player" value="<?= $this->printHtml($search); ?>">
        <button type="submit">Search</button>
    </form>

    <div class="floater">
        <table class="content-table encounter-table">
            <thead>
                <tr>
                    <th class="wf-100">Player</th>
                    <th>Characters</th>
                    <th>Achievement points</th>
                    <th>Member since</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td><a class="row-link" href="/player/profile/<?= $row['id']; ?>"><?= $this->printHtml($row['name']); ?></a></td>
                        <td><?= (int) $row['characters']; ?></td>
                        <td><?= \number_format((int) $row['points']); ?></td>
                        <td class="nowrap"><?= $row['created'] !== null ? \substr($row['created'], 0, 10) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)) : ?>
                    <tr><td colspan="4">No players found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
            $page    = $this->data['page'] ?? 1;
            $pages   = $this->data['pages'] ?? 1;
            $pageUrl = fn (int $p) : string => '/player/profile?' . \htmlspecialchars(\http_build_query(\array_filter(['player' => $search, 'page' => $p > 1 ? $p : null])));
            include __DIR__ . '/pager.tpl.php';
        ?>
    </div>
</div>
