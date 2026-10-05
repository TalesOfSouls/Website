<?php
/** @var \phpOMS\Views\View $this */
$item    = $this->data['item'];
$recipes = $this->data['recipes'] ?? [];
?>
<div class="floater">
    <div id="welcome-section">
        <p class="article-meta"><a class="text-link" href="/database">Database</a> &middot; Item #<?= $item->id; ?></p>
        <h1><?= $this->printHtml($item->name); ?></h1>
    </div>
</div>

<div class="floater page-section">
    <div class="layout-split right">
        <div>
            <div class="panel">
                <h2>Recipes</h2>
                <?php if (empty($recipes)) : ?>
                    <p class="muted">This item can't be crafted.</p>
                <?php endif; ?>
                <?php foreach ($recipes as $recipe) : ?>
                    <h3>Recipe #<?= $recipe->id; ?></h3>
                    <table class="content-table">
                        <thead><tr><th class="wf-100">Ingredient</th><th>Quantity</th></tr></thead>
                        <tbody>
                        <?php foreach ($recipe->ingredients as $ingredient) : ?>
                            <tr>
                                <td><a href="/database/item/<?= $ingredient->item->id; ?>"><?= $this->printHtml($ingredient->item->name); ?></a></td>
                                <td>1</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="muted">Cost: <?= $recipe->cost; ?> gold &middot; Time: <?= $recipe->time; ?>s</p>
                <?php endforeach; ?>
            </div>
            <div class="panel">
                <h2>Sources</h2>
                <p class="muted">Drop sources, vendors and quest rewards will be listed here.</p>
            </div>
        </div>
        <div class="panel">
            <h2>Details</h2>
            <dl class="stat-list">
                <dt>ID</dt><dd><?= $item->id; ?></dd>
                <dt>Type</dt><dd>-</dd>
                <dt>Tradable</dt><dd>-</dd>
                <dt>Stack size</dt><dd>-</dd>
            </dl>
            <p><a href="/wiki/item?id=<?= $item->id; ?>">Wiki article</a></p>
        </div>
    </div>
</div>
