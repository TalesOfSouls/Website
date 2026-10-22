<?php
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
$search  = \trim($this->request->getDataString('player') ?? '');
$samples = $this->data['sample'] ?? ['players' => []];

// Database accounts + sample players
$rows = [];
foreach ($this->data['profiles'] as $profile) {
    if ($profile->id == 1) { continue; }
    $rows[] = ['id' => $profile->id, 'name' => $profile->name, 'characters' => '-', 'points' => '-'];
}

foreach ($samples['players'] as $id => $player) {
    $points = \array_sum(\array_column(\Application\Frontend\SampleAchievements::forPlayer($id, $player['since']), 'points'));
    $rows[] = ['id' => $id, 'name' => $player['name'], 'characters' => \count($player['characters']), 'points' => \number_format($points)];
}
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Player list</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/player/profile">
        <input type="text" name="player" placeholder="Player" value="<?= $this->printHtml($search); ?>">
        <button type="submit">Search</button>
    </form>

    <div class="floater">
        <table class="content-table">
            <thead>
                <tr>
                    <th class="wf-100">Player</th>
                    <th>Characters</th>
                    <th>Achievement points</th>
                </tr>
            </thead>
            <tbody>
                <?php $c = 0; foreach ($rows as $row) :
                    if ($search !== '' && \stripos($row['name'], $search) === false) { continue; }
                    ++$c;
                    $url = UriFactory::build('{/base}/player/profile/' . $row['id']);
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($row['name']); ?></a></td>
                        <td><?= $row['characters']; ?></td>
                        <td><?= $row['points']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($c === 0) : ?>
                    <tr><td colspan="3">No players found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
