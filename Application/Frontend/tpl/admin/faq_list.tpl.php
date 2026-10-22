<?php
/** @var \phpOMS\Views\View $this */

/** @var \Models\Faq[] $faqs */
$faqs = $this->data['faqs'] ?? [];

include __DIR__ . '/nav.tpl.php';
?>
    <div class="panel">
        <h2>Questions <a class="button featured admin-create" href="/admin/faq/create">New question</a></h2>
        <table class="content-table encounter-table">
            <thead><tr><th>Category</th><th>Order</th><th class="wf-100">Question</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($faqs as $faq) : ?>
                <tr>
                    <td class="nowrap"><?= $this->printHtml($faq->category); ?></td>
                    <td><?= $faq->order; ?></td>
                    <td><a class="row-link" href="/admin/faq/<?= $faq->id; ?>"><?= $this->printHtml($faq->question); ?></a></td>
                    <td class="nowrap"><?= $faq->datetime->format('Y-m-d'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
