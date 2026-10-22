<?php
/**
 * Header and navigation of the administration pages
 *
 * Expects $this->data['section'] and $this->data['title']
 */
$sections = [
    'dashboard'   => ['/admin', 'Dashboard'],
    'news'        => ['/admin/news', 'News'],
    'wiki'        => ['/admin/wiki', 'Wiki'],
    'faq'         => ['/admin/faq', 'FAQ'],
    'development' => ['/admin/development', 'Development'],
    'feedback'    => ['/feedback', 'Feedback'],
    'users'       => ['/admin/users', 'Accounts'],
    'messages'    => ['/admin/messages', 'Messages'],
];

$section = $this->data['section'] ?? 'dashboard';
?>
<div class="floater">
    <div id="welcome-section">
        <h1><?= $this->printHtml($this->data['title'] ?? 'Administration'); ?></h1>
    </div>
</div>

<div class="floater page-section admin">
    <nav class="tabs admin-tabs">
        <?php foreach ($sections as $key => $entry) : ?>
            <a href="<?= $entry[0]; ?>"<?= $key === $section ? ' class="active"' : ''; ?>><?= $entry[1]; ?></a>
        <?php endforeach; ?>
    </nav>
