<?php
/** @var \phpOMS\Views\View $this */

/** @var \Models\News[] $news */
$news = $this->data['news'] ?? [];

include __DIR__ . '/nav.tpl.php';
?>
    <div class="panel">
        <h2>News posts <a class="button featured admin-create" href="/admin/news/create">New post</a></h2>
        <table class="content-table encounter-table">
            <thead><tr><th class="wf-100">Title</th><th>Status</th><th>Published</th><th>Author</th><th>Updated</th></tr></thead>
            <tbody>
            <?php foreach ($news as $post) : ?>
                <tr>
                    <td><a class="row-link" href="/admin/news/<?= $post->id; ?>"><?= $this->printHtml($post->title); ?></a></td>
                    <td>
                        <?php if (!$post->isVisible()) : ?>
                            <span class="badge"><?= $post->status === 'published' ? 'Scheduled' : 'Draft'; ?></span>
                        <?php else : ?>
                            <span class="badge done">Published</span>
                        <?php endif; ?>
                    </td>
                    <td class="nowrap"><?= $post->published->format('Y-m-d H:i'); ?></td>
                    <td class="nowrap"><?= $this->printHtml($post->account->name); ?></td>
                    <td class="nowrap"><?= $post->updated?->format('Y-m-d') ?? '-'; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($news)) : ?>
                <tr><td colspan="5">No news posts yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
