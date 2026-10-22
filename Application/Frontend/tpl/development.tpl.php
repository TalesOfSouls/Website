<?php
/** @var \phpOMS\Views\View $this */

/** @var \Models\Milestone[] $milestones */
$milestones = $this->data['milestones'] ?? [];

/** @var \Models\DevelopmentArea[] $areas */
$areas = $this->data['areas'] ?? [];

$stateTitles = ['done' => 'Done', 'progress' => 'In progress', 'planned' => 'Planned'];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Development</h1>
    </div>
</div>

<div class="floater page-section">
    <?php if ($this->data['admin'] ?? false) : ?>
        <p class="admin-bar"><a class="button" href="/admin/development">Edit roadmap &amp; progress</a></p>
    <?php endif; ?>
    <div class="layout-split right">
        <div class="panel">
            <h2>Roadmap</h2>
            <ul class="timeline">
                <?php foreach ($milestones as $milestone) : ?>
                <li class="<?= $this->printHtml($milestone->state); ?>">
                    <span class="when"><?= $this->printHtml($milestone->when); ?></span>
                    <span class="badge <?= $this->printHtml($milestone->state); ?>"><?= $stateTitles[$milestone->state] ?? $this->printHtml($milestone->state); ?></span>
                    <h4><?= $this->printHtml($milestone->title); ?></h4>
                    <p class="muted"><?= $this->printHtml($milestone->description); ?></p>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <div class="panel">
                <h2>Progress</h2>
                <?php foreach ($areas as $area) : ?>
                    <div><?= $this->printHtml($area->name); ?> <span class="muted">(<?= $area->progress; ?>%)</span></div>
                    <div class="bar"><span style="width: <?= \max(0, \min(100, $area->progress)); ?>%"></span></div>
                <?php endforeach; ?>
                <p class="muted">Rough estimates, updated with every dev update.</p>
            </div>
            <div class="panel">
                <h2>Latest updates</h2>
                <ul class="list">
                    <?php foreach ($this->data['news'] ?? [] as $news) : ?>
                        <li><a href="/news/<?= $this->printHtml($news['slug']); ?>"><?= $this->printHtml($news['title']); ?></a> <span class="muted"><?= $news['date']->format('Y-m-d'); ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="panel">
                <h2>Tech stack</h2>
                <ul class="list">
                    <li>Custom C/C++ engine</li>
                    <li>OpenGL &amp; DirectX renderer</li>
                    <li>Dedicated game servers</li>
                    <li>PHP website built on phpOMS</li>
                </ul>
            </div>
        </div>
    </div>
</div>
