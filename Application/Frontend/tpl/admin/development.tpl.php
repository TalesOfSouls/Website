<?php
/**
 * Roadmap and progress
 *
 * Every table row is its own form: the inputs reference the form in the last cell (form="...").
 */
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */

/** @var \Models\Milestone[] $milestones */
$milestones = $this->data['milestones'] ?? [];

/** @var \Models\DevelopmentArea[] $areas */
$areas = $this->data['areas'] ?? [];

$csrf   = UriFactory::getQuery('$CSRF') ?? '';
$states = ['done' => 'Done', 'progress' => 'In progress', 'planned' => 'Planned'];

$hidden = fn (int $id) : string => '<input type="hidden" name="CSRF" value="' . $csrf . '">'
    . '<input type="hidden" name="id" value="' . $id . '">'
    . '<input type="hidden" name="redirect" value="/admin/development">';

$milestoneRow = function (?\Models\Milestone $m) use ($states, $hidden) : string {
    $id   = $m?->id ?? 0;
    $form = 'milestone-' . ($id > 0 ? $id : 'new');

    $html = '<tr' . ($id === 0 ? ' class="new-row"' : '') . '>'
        . '<td><input form="' . $form . '" type="number" name="order" value="' . ($m?->order ?? (\count($this->data['milestones'] ?? []) + 1) * 10) . '" aria-label="Order"></td>'
        . '<td><input form="' . $form . '" type="text" name="when" maxlength="30" value="' . $this->printHtml($m?->when ?? '') . '" placeholder="TBA" aria-label="When"></td>'
        . '<td><input form="' . $form . '" type="text" name="title" maxlength="100" value="' . $this->printHtml($m?->title ?? '') . '" placeholder="New milestone" required aria-label="Title">'
        . '<input form="' . $form . '" type="text" name="description" maxlength="500" value="' . $this->printHtml($m?->description ?? '') . '" placeholder="Description" aria-label="Description"></td>'
        . '<td><select form="' . $form . '" name="state" aria-label="State">';

    foreach ($states as $key => $title) {
        $html .= '<option value="' . $key . '"' . (($m?->state ?? 'planned') === $key ? ' selected' : '') . '>' . $title . '</option>';
    }

    $html .= '</select></td><td class="row-actions">'
        . '<form id="' . $form . '" method="POST" action="/api/admin/milestone">' . $hidden($id)
        . '<button type="submit"' . ($id === 0 ? ' class="featured"' : '') . '>' . ($id === 0 ? 'Add' : 'Save') . '</button></form>';

    if ($id > 0) {
        $html .= '<form id="milestone-delete-' . $id . '" method="POST" action="/api/admin/milestone/delete" data-confirm="Delete this milestone?">'
            . $hidden($id) . '<button type="submit" class="danger" aria-label="Delete">&times;</button></form>';
    }

    return $html . '</td></tr>';
};

$areaRow = function (?\Models\DevelopmentArea $a) use ($hidden) : string {
    $id   = $a?->id ?? 0;
    $form = 'area-' . ($id > 0 ? $id : 'new');

    $html = '<tr' . ($id === 0 ? ' class="new-row"' : '') . '>'
        . '<td><input form="' . $form . '" type="number" name="order" value="' . ($a?->order ?? (\count($this->data['areas'] ?? []) + 1) * 10) . '" aria-label="Order"></td>'
        . '<td><input form="' . $form . '" type="text" name="name" maxlength="100" value="' . $this->printHtml($a?->name ?? '') . '" placeholder="New area" required aria-label="Name"></td>'
        . '<td><input form="' . $form . '" type="number" name="progress" min="0" max="100" value="' . ($a?->progress ?? 0) . '" required aria-label="Progress in %"></td>'
        . '<td class="row-actions">'
        . '<form id="' . $form . '" method="POST" action="/api/admin/area">' . $hidden($id)
        . '<button type="submit"' . ($id === 0 ? ' class="featured"' : '') . '>' . ($id === 0 ? 'Add' : 'Save') . '</button></form>';

    if ($id > 0) {
        $html .= '<form id="area-delete-' . $id . '" method="POST" action="/api/admin/area/delete" data-confirm="Delete this area?">'
            . $hidden($id) . '<button type="submit" class="danger" aria-label="Delete">&times;</button></form>';
    }

    return $html . '</td></tr>';
};

include __DIR__ . '/nav.tpl.php';
?>
    <div class="panel">
        <h2>Roadmap <a class="button admin-create" href="/development" target="_blank" rel="noopener">View page</a></h2>
        <table class="content-table admin-grid">
            <thead><tr><th>Order</th><th>When</th><th class="wf-100">Milestone</th><th>State</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($milestones as $milestone) { echo $milestoneRow($milestone); } ?>
                <?= $milestoneRow(null); ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>Progress</h2>
        <table class="content-table admin-grid">
            <thead><tr><th>Order</th><th class="wf-100">Area</th><th>Progress (%)</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($areas as $area) { echo $areaRow($area); } ?>
                <?= $areaRow(null); ?>
            </tbody>
        </table>
    </div>
</div>
