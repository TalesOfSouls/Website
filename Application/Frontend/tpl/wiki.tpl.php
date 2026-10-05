<?php

use phpOMS\Security\Guard;

/** @var \phpOMS\Views\View $this */
$wikiPath = $this->data['path'] ?? 'index';

// Navigation entries from the table of contents (wiki/toc.md)
\preg_match_all('/\[([^\]]+)\]\(([^)]+)\)/', (string) \file_get_contents(__DIR__ . '/wiki/toc.md'), $tocMatches, \PREG_SET_ORDER);

$currentLink = '/wiki' . ($wikiPath === 'index' ? '' : '/' . \str_replace('_', '/', $wikiPath));
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
                <li><a href="/wiki"<?= $currentLink === '/wiki' ? ' class="active"' : ''; ?>>Overview</a></li>
                <?php foreach ($tocMatches as $entry) : ?>
                    <li><a href="<?= $this->printHtml($entry[2]); ?>"<?= $currentLink === $entry[2] || \str_replace('/', '_', \ltrim($entry[2], '/')) === 'wiki_' . $wikiPath ? ' class="active"' : ''; ?>><?= $this->printHtml($entry[1]); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <article class="panel prose wiki-article">
            <?php
            if (Guard::isSafePath(__DIR__ . '/wiki/' . $wikiPath . '.tpl.php', __DIR__)
                && \is_file(__DIR__ . '/wiki/' . $wikiPath . '.tpl.php')
            ) {
                include __DIR__ . '/wiki/' . $wikiPath . '.tpl.php';
            } else {
                include __DIR__ . '/wiki/404.tpl.php';
            }
            ?>
        </article>
    </div>
</div>
