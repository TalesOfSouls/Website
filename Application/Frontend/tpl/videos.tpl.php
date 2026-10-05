<?php
$videos = [
    ['Dev Stream #3 - Program structure', '2024-07-15', 'Dev Stream', '/Application/Frontend/img/dusk.jpg'],
    ['Dev Stream #2 - First render', '2024-07-07', 'Dev Stream', '/Application/Frontend/img/sky.jpg'],
    ['Dev Stream #1 - Game documentation', '2024-07-01', 'Dev Stream', '/Application/Frontend/img/hills.jpg'],
    ['World concept - the Styx', '2024-06-20', 'Concept', '/Application/Frontend/img/mountain_side.jpg'],
    ['Class overview', '2024-06-10', 'Concept', '/Application/Frontend/img/hill_up.jpg'],
    ['Teaser', '2024-06-01', 'Trailer', '/Application/Frontend/img/dusk_2.jpg'],
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Videos</h1>
    </div>
</div>

<div class="floater page-section">
    <div class="notice info">The videos below are placeholders and will link to our channels once they are online.</div>

    <form class="filter-bar" method="GET" action="/videos">
        <select name="type">
            <option value="">All videos</option>
            <option>Dev Stream</option>
            <option>Trailer</option>
            <option>Concept</option>
            <option>Community</option>
        </select>
        <button type="submit">Filter</button>
        <a class="button" href="">YouTube</a>
        <a class="button" href="">Twitch</a>
    </form>

    <div class="card-grid">
        <?php foreach ($videos as $video) : ?>
        <a class="card" href="">
            <div class="video-thumb"><img src="<?= $video[3]; ?>" alt=""></div>
            <div class="card-body">
                <h5><?= $video[1]; ?> &middot; <?= $video[2]; ?></h5>
                <h3><?= $video[0]; ?></h3>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
