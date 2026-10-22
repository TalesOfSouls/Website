<?php
/** @var \phpOMS\Views\View $this */
$search = $this->data['search'] ?? '';
$rows   = $this->data['rows'] ?? [];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Guild list</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/guild/profile">
        <input type="text" name="guild" placeholder="Guild or tag" value="<?= $this->printHtml($search); ?>">
        <button type="submit">Search</button>
    </form>

    <div class="floater">
        <table class="content-table encounter-table">
            <thead>
                <tr>
                    <th class="wf-100">Guild</th>
                    <th>Tag</th>
                    <th>Server</th>
                    <th>Members</th>
                    <th>Recruiting</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td><a class="row-link" href="/guild/profile/<?= $row['id']; ?>"><?= $this->printHtml($row['name']); ?></a></td>
                        <td><?= $this->printHtml($row['tag']); ?></td>
                        <td class="nowrap"><?= $this->printHtml($row['server'] ?? '-'); ?></td>
                        <td><?= (int) $row['members']; ?></td>
                        <td><?= $row['recruiting'] ? '<span class="badge done">Yes</span>' : '<span class="badge">No</span>'; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)) : ?>
                    <tr><td colspan="5">No guilds found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
