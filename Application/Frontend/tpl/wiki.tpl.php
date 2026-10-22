<?php
/**
 * Wiki
 *
 * The articles are stored in the database (wiki_page). An article can have a module, a data block from the
 * game database that is rendered below the article (see wiki/module/*.tpl.php).
 */
use phpOMS\Utils\Parser\Markdown\Markdown;

/** @var \phpOMS\Views\View $this */
$slug  = $this->data['slug'] ?? 'index';
$admin = $this->data['admin'] ?? false;

/** @var null|\Models\WikiPage $page */
$page = $this->data['page'] ?? null;

/** @var \Models\WikiPage[] $nav */
$nav = $this->data['nav'] ?? [];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Wiki</h1>
    </div>
</div>

<div id="wiki" class="floater page-section">
    <div class="layout-split">
        <div class="panel wiki-nav">
            <form class="wiki-search" method="GET" action="/wiki/search">
                <input type="text" name="q" placeholder="Search the wiki" value="<?= $this->printHtml($this->request->getDataString('q') ?? ''); ?>">
            </form>
            <ul class="side-menu">
                <li><a href="/wiki"<?= $slug === 'index' ? ' class="active"' : ''; ?>>Overview</a></li>
                <?php foreach ($nav as $entry) : ?>
                    <li><a href="/wiki/<?= $this->printHtml($entry->slug); ?>"<?= $page !== null && $page->id === $entry->id ? ' class="active"' : ''; ?>><?= $this->printHtml($entry->navTitle !== '' ? $entry->navTitle : $entry->title); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php if ($admin) : ?>
                <p class="wiki-admin"><a class="button" href="/admin/wiki">Manage wiki</a></p>
            <?php endif; ?>
        </div>
        <article class="panel prose wiki-article">
            <?php if ($page !== null) : ?>
                <?php if ($admin) : ?>
                    <a class="button admin-edit" href="/admin/wiki/<?= $page->id; ?>">Edit</a>
                <?php endif; ?>
                <h1><?= $this->printHtml($page->title); ?></h1>
                <?= Markdown::parse($page->content); ?>
                <?php
                if ($page->module !== null && \preg_match('/^[a-z_]+$/', $page->module) === 1
                    && \is_file(__DIR__ . '/wiki/module/' . $page->module . '.tpl.php')
                ) {
                    include __DIR__ . '/wiki/module/' . $page->module . '.tpl.php';
                }
                ?>
                <p class="muted wiki-updated">Last updated <?= $page->updated->format('Y-m-d'); ?></p>
            <?php elseif ($slug === 'search') :
                $query   = $this->data['query'] ?? '';
                $results = $this->data['results'] ?? [];
            ?>
                <h1>Search</h1>
                <?php if ($query === '') : ?>
                    <p>Enter a search term in the navigation to search the wiki.</p>
                <?php elseif (empty($results)) : ?>
                    <p>No articles found for "<?= $this->printHtml($query); ?>".</p>
                <?php else : ?>
                    <p><?= \count($results); ?> article(s) found for "<?= $this->printHtml($query); ?>":</p>
                    <ul>
                        <?php foreach ($results as $result) : ?>
                            <li><a href="/wiki<?= $result->slug === 'index' ? '' : '/' . $this->printHtml($result->slug); ?>"><?= $this->printHtml($result->title); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($query !== '') : ?>
                    <p>Looking for a specific item? Try the <a href="/database?q=<?= \urlencode($query); ?>">item database</a>.</p>
                <?php endif; ?>
            <?php elseif ($slug === 'item') : ?>
                <?php include __DIR__ . '/wiki/item.tpl.php'; ?>
            <?php else : ?>
                <?php include __DIR__ . '/wiki/404.tpl.php'; ?>
                <?php if ($admin && \preg_match('/^[a-z0-9_\/-]+$/', $slug) === 1) : ?>
                    <p><a class="button featured" href="/admin/wiki/create?slug=<?= \urlencode($slug); ?>">Create this article</a></p>
                <?php endif; ?>
            <?php endif; ?>
        </article>
    </div>
</div>
