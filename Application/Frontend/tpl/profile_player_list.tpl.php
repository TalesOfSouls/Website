<?php
use phpOMS\Uri\UriFactory;
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Player list</h1>
    </div>
</div>

<div id="ladder">
    <div class="floater">
        <input type="text" name="player" placeholder="Player">
    </div>

    <div class="floater">
        <table class="content-table">
            <thead>
                <tr>
                    <th class="wf-100">Player</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->data['profiles'] as $profile) :
                    if ($profile->id == 1) { continue; } 
                    $url = UriFactory::build('{/base}/player/profile/' . $profile->id);
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($profile->name); ?></a>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>