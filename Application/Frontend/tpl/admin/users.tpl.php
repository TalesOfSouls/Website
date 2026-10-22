<?php
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */

/** @var \Models\Account[] $accounts */
$accounts = $this->data['accounts'] ?? [];
$roles    = $this->data['roles'] ?? [];
$states   = $this->data['states'] ?? [];
$search   = $this->data['search'] ?? '';
$role     = $this->data['role'] ?? '';
$csrf     = UriFactory::getQuery('$CSRF') ?? '';
$self     = $this->data['admin']->id ?? 0;

include __DIR__ . '/nav.tpl.php';
?>
    <form class="filter-bar" method="GET" action="/admin/users">
        <input type="text" name="q" placeholder="Account name" value="<?= $this->printHtml($search); ?>">
        <select name="role">
            <option value="">Any role</option>
            <?php foreach ($roles as $key => $title) : ?>
                <option value="<?= $key; ?>"<?= (string) $key === $role ? ' selected' : ''; ?>><?= $title; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Search</button>
    </form>

    <div class="panel">
        <h2>Accounts</h2>
        <table class="content-table admin-grid">
            <thead><tr><th>#</th><th class="wf-100">Account</th><th>E-mail</th><th>Created</th><th>Last sign in</th><th>Role</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($accounts as $account) :
                $form = 'account-' . $account->id;
                $own  = $account->id === $self;
            ?>
                <tr>
                    <td><?= $account->id; ?></td>
                    <td><a href="/player/profile/<?= $account->id; ?>"><?= $this->printHtml($account->name); ?></a><?= $own ? ' <small class="muted">(you)</small>' : ''; ?></td>
                    <td class="nowrap"><?= $this->printHtml($account->email); ?></td>
                    <td class="nowrap"><?= $account->created?->format('Y-m-d') ?? '-'; ?></td>
                    <td class="nowrap"><?= $account->lastLogin?->format('Y-m-d H:i') ?? '-'; ?></td>
                    <td>
                        <select form="<?= $form; ?>" name="role" aria-label="Role"<?= $own ? ' disabled' : ''; ?>>
                            <?php foreach ($roles as $key => $title) : ?>
                                <option value="<?= $key; ?>"<?= $key === $account->role ? ' selected' : ''; ?>><?= $title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select form="<?= $form; ?>" name="status" aria-label="Status"<?= $own ? ' disabled' : ''; ?>>
                            <?php foreach ($states as $key => $title) : ?>
                                <option value="<?= $key; ?>"<?= $key === $account->status ? ' selected' : ''; ?>><?= $title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="row-actions">
                        <?php if (!$own) : ?>
                        <form id="<?= $form; ?>" method="POST" action="/api/admin/account">
                            <input type="hidden" name="CSRF" value="<?= $csrf; ?>">
                            <input type="hidden" name="id" value="<?= $account->id; ?>">
                            <input type="hidden" name="redirect" value="<?= $this->printHtml('/admin/users?' . \http_build_query(\array_filter(['q' => $search, 'role' => $role, 'page' => $this->data['page'] ?? 1]))); ?>">
                            <button type="submit">Save</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($accounts)) : ?>
                <tr><td colspan="8">No accounts found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
            $page    = $this->data['page'] ?? 1;
            $pages   = $this->data['pages'] ?? 1;
            $pageUrl = fn (int $p) : string => '/admin/users?' . \htmlspecialchars(\http_build_query(\array_filter(['q' => $search, 'role' => $role, 'page' => $p > 1 ? $p : null])));
            include __DIR__ . '/../pager.tpl.php';
        ?>
        <p class="muted chart-note">Developers can moderate the feedback board, administrators can use the whole administration. Banned accounts can't sign in.</p>
    </div>
</div>
