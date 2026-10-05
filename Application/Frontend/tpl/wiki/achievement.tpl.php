<?php

use Models\AchievementTypeMapper;

$achievements = AchievementTypeMapper::getAll()->executeGetArray();
?>
<h1>Achievements</h1>

<p>Achievements reward you for exploring, fighting, crafting and competing. Most achievements have up to
five tiers - every tier raises the requirement and grants a better reward such as titles, cosmetics or
achievement points that count towards the <a href="/ladder">achievement ladder</a>.</p>

<h2>Categories</h2>
<ul>
    <li><strong>Progression</strong> - reach the maximum level, complete story chapters</li>
    <li><strong>Combat</strong> - defeat monsters, bosses and other players</li>
    <li><strong>Exploration</strong> - discover locations, dungeons and hidden places</li>
    <li><strong>Economy</strong> - collect gold, trade and craft</li>
    <li><strong>Collection</strong> - items, pets, mounts and titles</li>
</ul>

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
