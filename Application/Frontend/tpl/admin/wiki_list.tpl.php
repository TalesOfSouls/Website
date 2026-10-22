<?php
/** @var \phpOMS\Views\View $this */

/** @var \Models\WikiPage[] $pages */
$pages = $this->data['pages'] ?? [];

include __DIR__ . '/nav.tpl.php';
?>
    <div class="panel">
        <h2>Wiki articles <a class="button featured admin-create" href="/admin/wiki/create">New article</a></h2>
        <table class="content-table encounter-table">
            <thead><tr><th>Nav</th><th class="wf-100">Title</th><th>Url</th><th>Module</th><th>Updated</th></tr></thead>
            <tbody>
            <?php foreach ($pages as $page) : ?>
                <tr>
                    <td><?= $page->navOrder ?? '-'; ?></td>
                    <td><a class="row-link" href="/admin/wiki/<?= $page->id; ?>"><?= $this->printHtml($page->title); ?></a></td>
                    <td class="nowrap">/wiki<?= $page->slug === 'index' ? '' : '/' . $this->printHtml($page->slug); ?></td>
                    <td><?= $this->printHtml($page->module ?? '-'); ?></td>
                    <td class="nowrap"><?= $page->updated->format('Y-m-d'); ?> <small class="muted"><?= $this->printHtml($page->account->name); ?></small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="muted chart-note">Articles with a navigation position are shown in the wiki navigation. "index" is the wiki overview.</p>
    </div>
</div>
