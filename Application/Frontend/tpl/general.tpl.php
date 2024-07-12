<?php

/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Web\Backend
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/** @var Web\Backend\BackendView $this */
/** @var phpOMS\Model\Html\Head $head */
$head = $this->head;

/** @var array $dispatch */
$dispatch = $this->data['dispatch'] ?? [];
?>
<!DOCTYPE HTML>
<html lang="<?= $this->printHtml($this->response->header->l11n->language); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#343a40">
    <meta name="msapplication-navbutton-color" content="#343a40">
    <meta name="apple-mobile-web-app-status-bar-style" content="#343a40">
    <link rel="icon" type="image/png" sizes="512x512" href="Application/Frontend/img/icon-512x512.png">
    <link rel="icon" type="image/png" sizes="128x128" href="Application/Frontend/img/icon-128x128.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Application/Frontend/img/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="Application/Frontend/img/icon-16x16.png">
    <meta name="description" content="<?= $this->getHtml(':meta', '0', '0'); ?>">
    <?= $head->meta->render(); ?>

    <base href="/">

    <link rel="manifest" href="<?= UriFactory::build('Application/Frontend/manifest.json'); ?>">
    <link rel="manifest" href="<?= UriFactory::build('Application/Frontend/manifest.webmanifest'); ?>">
    <link rel="shortcut icon" href="<?= UriFactory::build('Application/Frontend/img/favicon.ico?v=1.0.0'); ?>" type="image/x-icon">

    <title><?= $this->printHtml($head->title); ?></title>

    <?= $head->renderAssets(); ?>

    <style><?= $head->renderStyle(); ?></style>
    <script><?= $head->renderScript(); ?></script>
</head>
<body>
<div>
    <div id="video-pane">
        <img id="header-video" src="/Application/Frontend/img/dusk_2.jpg">
    </div>
    <?php include __DIR__ . '/header.php'; ?>
</div>
<main>
<?php
    $c = 0;
    foreach ($dispatch as $view) {
        if (!($view instanceof \phpOMS\Views\NullView)
            && $view instanceof \phpOMS\Contract\RenderableInterface
        ) {
            $render = $view->render();
            if ($render === '') {
                continue;
            }

            echo $render;
            ++$c;
        }
    }

    if ($c === 0) {
        include __DIR__ . '/Error/404.tpl.php';
    }
?>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<?= $head->renderAssetsLate(); ?>
