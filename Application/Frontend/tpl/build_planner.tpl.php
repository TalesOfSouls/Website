<?php
use Models\PlayerClassMapper;

$classes = PlayerClassMapper::getAll()->executeGetArray();

$time = time();
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Build Planner</h1>
    </div>
</div>

<div id="character" class="side-nav-content">
    <div class="floater">
        <div>
            <div class="main-content">
                <select>
                    <option disabled selected>Class
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
                Character view
            </div>
            <div class="main-content">
                Stats view
            </div>
        </div>
        <div>
            <div class="main-content">
                equipment
                skill tree

            </div>
        </div>
    </div>
</div>