<?php
/** @var \phpOMS\Views\View $this */
$areas = [
    ['Engine core', 45],
    ['Rendering', 30],
    ['Networking', 15],
    ['Game systems', 20],
    ['World &amp; content', 10],
    ['Website', 40],
];

$roadmap = [
    ['done', 'Jul 2024', 'Game documentation', 'Concept, story and game systems are documented.'],
    ['done', 'Jul 2024', 'First render', 'The engine opens a window and renders its first frame.'],
    ['done', 'Jul 2024', 'Program structure', 'Hot reloadable game code, renderer abstraction, memory pools.'],
    ['progress', '2026', 'Website', 'News, wiki, database, ladders and community pages.'],
    ['planned', 'TBA', 'Beta 0.1.0 Dev Stream', 'A live look at the first playable build.'],
    ['planned', 'TBA', 'Beta 0.1.0 Release', 'First closed beta for supporters.'],
    ['planned', 'TBA', 'Release Dev Stream', 'Final preparations for the launch.'],
    ['planned', 'TBA', 'Reset &amp; Release 1.0.0', 'Progress reset and official release.'],
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Development</h1>
    </div>
</div>

<div class="floater page-section">
    <div class="layout-split right">
        <div class="panel">
            <h2>Roadmap</h2>
            <ul class="timeline">
                <?php foreach ($roadmap as $entry) : ?>
                <li class="<?= $entry[0]; ?>">
                    <span class="when"><?= $entry[1]; ?></span>
                    <span class="badge <?= $entry[0]; ?>"><?= $entry[0] === 'done' ? 'Done' : ($entry[0] === 'progress' ? 'In progress' : 'Planned'); ?></span>
                    <h4><?= $entry[2]; ?></h4>
                    <p class="muted"><?= $entry[3]; ?></p>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <div class="panel">
                <h2>Progress</h2>
                <?php foreach ($areas as $area) : ?>
                    <div><?= $area[0]; ?> <span class="muted">(<?= $area[1]; ?>%)</span></div>
                    <div class="bar"><span style="width: <?= $area[1]; ?>%"></span></div>
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
