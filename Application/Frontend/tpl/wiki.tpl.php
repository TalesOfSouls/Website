<?php

use phpOMS\Security\Guard;
use phpOMS\Utils\Parser\Markdown\Markdown;

?>
<div class="floater">
    <div id="welcome-section">
        <h1>Wiki</h1>
    </div>
</div>

<div id="wiki" class="side-nav-content">
    <div class="floater">
        <div>
            <div class="side-nav opaque-area">
                <div>
                    <input type="text">
                </div>
                <div>
                    <?= Markdown::parse(\file_get_contents(__DIR__ . '/wiki/toc.md')); ?>
                </div>
            </div>
        </div>
        <div>
            <article class="opaque-area">
                <?php
                if (Guard::isSafePath(__DIR__ . '/wiki/' . $this->data['path'] . '.tpl.php', __DIR__)
                    && \is_file(__DIR__ . '/wiki/' . $this->data['path'] . '.tpl.php')
                ) {
                    include __DIR__ . '/wiki/' . $this->data['path'] . '.tpl.php';
                } else {
                    include __DIR__ . '/wiki/404.tpl.php';
                }
                ?>
            </article>
        </div>
    </div>
</div>