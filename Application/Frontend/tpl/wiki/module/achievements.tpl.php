<?php

use Models\AchievementTypeMapper;

$achievements = AchievementTypeMapper::getAll()->executeGetArray();
?>
<h2>All achievements</h2>
<?php if (empty($achievements)) : ?>
    <p><em>No achievements have been published yet.</em></p>
<?php else : ?>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Achievement</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($achievements as $achievement) : ?>
        <tr>
            <td><?= $achievement->id; ?></td>
            <td><?= $this->printHtml($achievement->name); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
