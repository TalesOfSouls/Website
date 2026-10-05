<?php
/** @var \phpOMS\Views\View $this */
?>
<div class="floater">
    <div id="welcome-section">
        <p class="article-meta"><a class="text-link" href="/database">Database</a> &middot; Skill #<?= (int) ($this->data['id'] ?? 0); ?></p>
        <h1>Skill</h1>
        <p>Skills are not published yet. Once the class designs are finalized every skill will get its own page
        with damage values, cooldowns and modifiers.</p>
        <p><a class="button" href="/wiki/skill">About skills</a><a class="button" href="/build/planner">Build planner</a></p>
    </div>
</div>
