<?php
/**
 * Account settings of the signed in user
 */
use Application\Frontend\Provider\FeedbackData;
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */

/** @var null|\Models\Account $account */
$account = $this->data['account'] ?? null;
if ($account === null) {
    return;
}

$csrf       = UriFactory::getQuery('$CSRF') ?? '';
$characters = $this->data['characters'] ?? [];

/** @var \Models\Feedback[] $feedback */
$feedback = $this->data['feedback'] ?? [];

$roles = [0 => 'Player', 1 => 'Developer', 2 => 'Administrator'];
?>
<div class="floater">
    <div class="profile-banner">
        <div class="profile-head">
            <div class="avatar"><?= $this->printHtml(\mb_substr($account->name, 0, 1)); ?></div>
            <div>
                <h1 class="profile-name"><?= $this->printHtml($account->name); ?></h1>
                <p class="profile-meta">
                    <?= $roles[$account->role] ?? 'Player'; ?>
                    &middot; Member since <?= $account->created?->format('Y-m-d') ?? '-'; ?>
                    &middot; <a class="text-link" href="/player/profile/<?= $account->id; ?>">Public profile</a>
                    <?php if ($account->isAdmin()) : ?>&middot; <a class="text-link" href="/admin">Administration</a><?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="floater page-section">
    <div class="layout-halves">
        <div class="panel">
            <h2>E-mail address</h2>
            <form id="account-email-form" class="site-form wide" method="POST" action="/api/account/update">
                <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                <input type="hidden" name="redirect" value="/account">
                <div class="form-group">
                    <label for="account-email">E-mail</label>
                    <input id="account-email" type="email" name="email" autocomplete="email" value="<?= $this->printHtml($account->email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="account-email-password">Current password</label>
                    <input id="account-email-password" type="password" name="current_password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="featured">Change e-mail</button>
            </form>
        </div>

        <div class="panel">
            <h2>Password</h2>
            <form id="account-password-form" class="site-form wide" method="POST" action="/api/account/update">
                <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                <input type="hidden" name="redirect" value="/account">
                <div class="form-group">
                    <label for="account-current-password">Current password</label>
                    <input id="account-current-password" type="password" name="current_password" autocomplete="current-password" required>
                </div>
                <div class="form-group">
                    <label for="account-new-password">New password</label>
                    <input id="account-new-password" type="password" name="password" autocomplete="new-password" minlength="10" required>
                    <span class="form-hint">At least 10 characters.</span>
                </div>
                <div class="form-group">
                    <label for="account-new-password2">Repeat new password</label>
                    <input id="account-new-password2" type="password" name="password_repeat" autocomplete="new-password" minlength="10" required>
                </div>
                <button type="submit" class="featured">Change password</button>
            </form>
        </div>
    </div>

    <div class="layout-halves">
        <div class="panel">
            <h2>Characters</h2>
            <table class="content-table">
                <thead><tr><th class="wf-100">Character</th><th>Class</th><th>Level</th><th>Server</th></tr></thead>
                <tbody>
                <?php foreach ($characters as $character) : ?>
                    <tr>
                        <td>
                            <a href="/player/character/profile/<?= $character['id']; ?>"><?= $this->printHtml($character['name']); ?></a>
                            <?php if ($character['season'] !== null) : ?><span class="badge planned">Seasonal</span><?php endif; ?>
                        </td>
                        <td><?= $this->printHtml($character['class']); ?></td>
                        <td><?= $character['level']; ?></td>
                        <td class="nowrap"><?= $this->printHtml($character['server']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($characters)) : ?>
                    <tr><td colspan="4">No characters yet. Characters are created in the game.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Your feedback</h2>
            <table class="content-table encounter-table">
                <thead><tr><th class="wf-100">Title</th><th>State</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($feedback as $entry) : ?>
                    <tr>
                        <td><a class="row-link" href="/feedback/<?= $entry->id; ?>"><?= $this->printHtml($entry->title); ?></a></td>
                        <td><span class="badge <?= $this->printHtml($entry->state); ?>"><?= FeedbackData::STATES[$entry->state] ?? $this->printHtml($entry->state); ?></span></td>
                        <td class="nowrap"><?= $entry->created->format('Y-m-d'); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($feedback)) : ?>
                    <tr><td colspan="3">No feedback yet. <a href="/feedback/create">Submit feedback</a></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
