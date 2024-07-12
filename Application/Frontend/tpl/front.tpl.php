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

use phpOMS\Application\ApplicationStatus;
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
        <video id="header-video" src="https://videos.pexels.com/video-files/7710243/7710243-hd_1280_720_30fps.mp4" loop="loop" autoplay="autoplay" playsinline="playsinline"></video>
    </div>
    <?php include __DIR__ . '/header.php'; ?>
</div>
<main>
    <div class="floater">
        <div id="welcome-section">
            <h1>Tales of Souls Development</h1>
            <p>Follow our development progress through our news posts, on our discord or live streams. If you would like to you can also support us on patreon.</p>
            <p><a class="button" href="">Discord</a><a class="button featured" href="">Patreon</a></p>
        </div>

        <div id="news-section">
            <h2>Latest<a href="more">more</a></h2>
            <div class="flex-row">
                <div class="col-3">
                    <div class="bg-img">
                        <img src="/Application/Frontend/img/hills.jpg">
                    </div>
                    <div>
                        <h5>Date</h5>
                        <h3></h3>
                    </div>
                </div>

                <div class="col-3">
                    <div class="bg-img">
                        <img src="/Application/Frontend/img/sky.jpg">
                    </div>
                    <div>
                        <h5>Date</h5>
                        <h3></h3>
                    </div>
                </div>

                <div class="col-3">
                    <div class="bg-img">
                        <img src="/Application/Frontend/img/mountain_side.jpg">
                    </div>
                    <div>
                        <h5>Date</h5>
                        <h3></h3>
                    </div>
                </div>

                <div class="col-3">
                    <div class="bg-img">
                        <img src="/Application/Frontend/img/dusk.jpg">
                    </div>
                    <div>
                        <h5>Date</h5>
                        <h3></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="front-section front-section-a">
        <div class="bg-img">
            <img src="/Application/Frontend/img/sky.jpg">
        </div>
        <div class="floater">
            <h4></h4>
            <h1>Races</h1>
            <p>Find out more about the races in Tales of Souls, their history, affiliations and design.</p>
            <p><a class="button" href="">MORE</a></p>
        </div>
    </div>
    <div class="front-section front-section-b">
        <div class="bg-img">
            <img src="/Application/Frontend/img/hill_up.jpg">
        </div>
        <div class="floater">
            <h4></h4>
            <h1>Classes</h1>
            <p>Learn more about the different classes, their strengths weaknesses and design.</p>
            <p><a class="button" href="">MORE</a></p>
        </div>
    </div>
    <div class="front-section front-section-a">
        <div class="bg-img">
            <img src="/Application/Frontend/img/dusk.jpg">
        </div>
        <div class="floater">
            <h4></h4>
            <h1>Items</h1>
            <p>A vast amount of items await you with many unique fighting styles and designs.</p>
            <p><a class="button" href="">MORE</a></p>
        </div>
    </div>
    <div class="front-section front-section-b">
        <div class="bg-img">
            <img src="/Application/Frontend/img/dusk_2.jpg">
        </div>
        <div class="floater">
            <h4></h4>
            <h1>Programming</h1>
            <p>;laksjdfka sdfkla jdfkajdsfakjwejioa djfoiasjdfklasdjf iwejfijakghasjd fksadf</p>
            <p><a class="button" href="">MORE</a></p>
        </div>
    </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<?= $head->renderAssetsLate(); ?>
