<?php
/**
 * Pager
 *
 * Expects:
 *   $page    int      Current page
 *   $pages   int      Number of pages
 *   $pageUrl callable Url of a page (int $page) : string
 */
if ($pages <= 1) {
    return;
}
?>
<nav class="pager">
    <?php if ($page > 1) : ?><a href="<?= $pageUrl($page - 1); ?>" aria-label="Previous page">&laquo;</a><?php endif; ?>
    <?php for ($p = \max(1, $page - 3); $p <= \min($pages, $page + 3); ++$p) : ?>
        <?php if ($p === $page) : ?>
            <span class="current"><?= $p; ?></span>
        <?php else : ?>
            <a href="<?= $pageUrl($p); ?>"><?= $p; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($page < $pages) : ?><a href="<?= $pageUrl($page + 1); ?>" aria-label="Next page">&raquo;</a><?php endif; ?>
</nav>
