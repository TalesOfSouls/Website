<?php
/** @var \phpOMS\Views\View $this */
$items  = $this->data['items'] ?? [];
$page   = $this->data['page'] ?? 1;
$pages  = $this->data['pages'] ?? 1;
$search = $this->data['search'] ?? '';

$pageUrl = function (int $p) use ($search) : string {
    return '/database?' . \http_build_query(\array_filter(['q' => $search, 'page' => $p > 1 ? $p : null]));
};
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Database</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/database">
        <input type="text" name="q" placeholder="Search items" value="<?= $this->printHtml($search); ?>">
        <button type="submit">Search</button>
        <?php if ($search !== '') : ?>
            <a class="button" href="/database">Reset</a>
        <?php endif; ?>
    </form>

    <div class="floater">
        <p class="muted"><?= \number_format($this->data['total'] ?? 0); ?> items &middot; page <?= $page; ?> of <?= $pages; ?></p>

        <table class="content-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th class="wf-100">Item</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item) : ?>
                <tr>
                    <td><?= $item->id; ?></td>
                    <td><a href="/database/item/<?= $item->id; ?>"><?= $this->printHtml($item->name); ?></a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($items)) : ?>
                <tr><td colspan="2" class="muted">No items found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($pages > 1) : ?>
        <nav class="pager">
            <?php if ($page > 1) : ?><a href="<?= $pageUrl($page - 1); ?>">&laquo;</a><?php endif; ?>
            <?php for ($p = \max(1, $page - 3); $p <= \min($pages, $page + 3); ++$p) : ?>
                <?php if ($p === $page) : ?>
                    <span class="current"><?= $p; ?></span>
                <?php else : ?>
                    <a href="<?= $pageUrl($p); ?>"><?= $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $pages) : ?><a href="<?= $pageUrl($page + 1); ?>">&raquo;</a><?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</div>
