<?php

use Models\ItemMapper;
use Models\RecipeMapper;

$item = ItemMapper::get()
    ->where('id', $this->request->getDataInt('id'))
    ->executeGet();

/**
 * @var \Models\Recipe[] $recipes
 */
$recipes = RecipeMapper::getAll()
    ->with('ingredients')
    ->with('ingredients/item')
    ->where('for', $this->request->getDataInt('id'))
    ->executeGetArray();

/**
 * @var \Models\Recipe[] $usedin
 */
$usedin = RecipeMapper::getAll()
    ->with('for')
    ->with('ingredients')
    ->where('ingredients/item', $this->request->getDataInt('id'))
    ->executeGetArray();

$time = time();
?>

<h1><?= $this->printHtml($item->name); ?></h1>

<p>See this item in the <a href="/database/item/<?= $item->id; ?>">item database</a>.</p>

<h2>Recipes</h2>

<?php if (empty($recipes)) : ?>
    <p><em>This item can't be crafted.</em></p>
<?php endif; ?>

<?php foreach ($recipes as $recipe) : ?>
    <h3>#<?= $recipe->id; ?></h3>
    <p>Cost: <?= $recipe->cost; ?> &middot; Time: <?= $recipe->time; ?>s</p>
    <p>Ingredients:</p>
    <table>
        <tr>
            <th>Item
            <th>Quantity
        <?php foreach ($recipe->ingredients as $ingredient) : ?>
        <tr>
            <td><?= $this->printHtml($ingredient->item->name); ?>
            <td>1
        <?php endforeach; ?>
    </table>
<?php endforeach; ?>

<?php if (false && !empty($usedin)) : /* @todo the "used in" query returns wrong recipes */ ?>
    <h2>Used in recipes</h2>

    <table>
        <?php foreach (/*$usedin*/[] as $in) : ?>
            <tr>
                <td><?= $this->printHtml($in->for->name); ?>
        <?php endforeach; ?>
    </table>
<?php endif; ?>