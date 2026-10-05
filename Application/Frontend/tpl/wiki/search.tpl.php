<?php
/** @var \phpOMS\Views\View $this */
$query   = \trim($this->request->getDataString('q') ?? '');
$results = [];

if ($query !== '') {
    \preg_match_all('/\[([^\]]+)\]\(([^)]+)\)/', (string) \file_get_contents(__DIR__ . '/toc.md'), $matches, \PREG_SET_ORDER);

    foreach ($matches as $match) {
        if (\stripos($match[1], $query) !== false) {
            $results[] = ['title' => $match[1], 'link' => $match[2]];
        }
    }
}
?>
<h1>Search</h1>

<?php if ($query === '') : ?>
    <p>Enter a search term in the navigation to search the wiki.</p>
<?php elseif (empty($results)) : ?>
    <p>No articles found for "<?= $this->printHtml($query); ?>".</p>
    <p>Looking for a specific item? Try the <a href="/database?q=<?= \urlencode($query); ?>">item database</a>.</p>
<?php else : ?>
    <p><?= \count($results); ?> article(s) found for "<?= $this->printHtml($query); ?>":</p>
    <ul>
        <?php foreach ($results as $result) : ?>
            <li><a href="<?= $this->printHtml($result['link']); ?>"><?= $this->printHtml($result['title']); ?></a></li>
        <?php endforeach; ?>
    </ul>
    <p>Looking for a specific item? Try the <a href="/database?q=<?= \urlencode($query); ?>">item database</a>.</p>
<?php endif; ?>
