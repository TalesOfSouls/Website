<?php
/** @var \Application\Frontend\AppView $this */
$account = $this->data['account'] ?? null;
$flash   = $this->data['flash'] ?? null;
?>
<div class="floater">
    <header>
        <div id="company">Tales of Souls</div>
        <nav id="top-nav">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/news">News</a></li>
                <li><a href="/game">Game</a></li>
                <li><a href="/wiki">Wiki</a></li>
                <li><a href="/faq">FAQ</a></li>
                <li><a href="/patreon">Support</a></li>
                <!--<li><a href="/shop">Buy</a></li>-->
                <?php if ($account !== null && $account->id > 0) : ?>
                    <?php if ($account->isAdmin()) : ?><li><a href="/admin">Admin</a></li><?php endif; ?>
                    <li><a href="/account" title="Account settings"><?= $this->printHtml($account->name); ?></a></li>
                    <li><a href="/api/account/signout">Sign out</a></li>
                <?php else : ?>
                    <li><a href="/signin">Sign in</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
</div>

<div id="app-message-container" class="app-messages" aria-live="polite">
    <template id="app-message-tpl">
        <div class="log-msg">
            <button type="button" class="close" aria-label="Close">&times;</button>
            <strong class="log-msg-title"></strong>
            <div class="log-msg-content"></div>
        </div>
    </template>
    <?php if ($flash !== null) : ?>
        <div class="log-msg log-lvl-<?= $this->printHtml($flash['status']); ?> flash">
            <?php if (!empty($flash['title'])) : ?><strong class="log-msg-title"><?= $this->printHtml($flash['title']); ?></strong><?php endif; ?>
            <div class="log-msg-content"><?= $this->printHtml($flash['message']); ?></div>
        </div>
    <?php endif; ?>
</div>
