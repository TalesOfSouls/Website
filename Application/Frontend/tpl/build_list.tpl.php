<?php
use Models\PlayerClassMapper;

$classes = PlayerClassMapper::getAll()->executeGetArray();

$time = time();
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Builds</h1>
    </div>
</div>

<div id="ladder">
    <div class="floater">
        <select>
            <option disabled selected>Class
            <option>Any
            <?php foreach ($classes as $category) : ?>
            <?php if ($category->parent === null
                && ($category->releaseDate === null || $category->releaseDate->getTimestamp() < $time)) : ?>
                <optgroup label="<?= $this->printHtml($category->name); ?>">
                <?php foreach ($classes as $cl) : ?>
                    <?php if ($cl->parent === $category->id
                        && ($cl->releaseDate === null || $cl->releaseDate->getTimestamp() < $time)) : ?>
                        <option><?= $this->printHtml($cl->name); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
                </optgroup>
            <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <select>
            <option disabled selected>Mode
            <option>Any
            <optgroup label="PvP">
                <option>PvP Arena
                <option>PvP WvW
            </optgroup>
            <option>Open World
            <option>Dungeons
            <option>Raids
        </select>

        <input type="text" name="name" placeholder="Name">
    </div>

    <div class="floater">
        <table class="content-table">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Mode</th>
                    <th class="wf-100">Name</th>
                    <th>Creator</th>
                    <th>Updated</th>
                    <th>Followers</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i < 21; ++$i) : ?>
                <tr>
                    <td>Class</td>
                    <td>Mode</td>
                    <td>Name</td>
                    <td>Creator</td>
                    <td>Updated</td>
                    <td>Followers</td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>