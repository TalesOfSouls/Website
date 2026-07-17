<?php
use phpOMS\Uri\UriFactory;
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Guild list</h1>
    </div>
</div>

<div id="ladder">
    <div class="floater">
        <input type="text" name="player" placeholder="Guild">

        <input type="text" name="server" list="server_names" placeholder="Server">
        <datalist id="server_names">
            <option value="Boston">
            <option value="Cambridge">
        </datalist>
    </div>

    <div class="floater">
        <table class="content-table">
            <thead>
                <tr>
                    <th class="wf-100">Guild</th>
                    <th>Server</th>
                    <th>Members</th>
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