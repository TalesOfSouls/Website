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
    <div class="floater">
    <header>
        <div id="company">Tales of Souls</div>
        <nav id="top-nav">
            <ul>
                <li><a href="">Home</a></li>
                <li><a href="">Map</a></li>
                <li><a href="">Wiki</a></li>
                <li><a href="">Buy</a></li>
                <li><a href="">Login</a></li>
            </ul>
        </nav>
    </header>
    </div>
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
                    <img src="https://images.pexels.com/photos/1279813/pexels-photo-1279813.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1">
                    <div>
                        <h5>Date</h5>
                        <h3></h3>
                    </div>
                </div>

                <div class="col-3">
                    <img src="https://images.pexels.com/photos/1153895/pexels-photo-1153895.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1">
                    <div>
                        <h5>Date</h5>
                        <h3></h3>
                    </div>
                </div>

                <div class="col-3">
                    <img src="https://images.pexels.com/photos/2541310/pexels-photo-2541310.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1">
                    <div>
                        <h5>Date</h5>
                        <h3></h3>
                    </div>
                </div>

                <div class="col-3">
                    <img src="https://images.pexels.com/photos/1621793/pexels-photo-1621793.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1">
                    <div>
                        <h5>Date</h5>
                        <h3></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="section front-section-a">
        <img src="https://images.pexels.com/photos/417074/pexels-photo-417074.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1">
        <div class="floater">
            <h4></h4>
            <h1></h1>
            <p><a class="button" href="">MORE</a></p>
        </div>
    </div>
    <div class="section front-section-b">
        <img src="https://images.pexels.com/photos/1109352/pexels-photo-1109352.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1">
        <div class="floater">
            <h4></h4>
            <h1></h1>
            <p><a class="button" href="">MORE</a></p>
        </div>
    </div>
    <div class="section front-section-a">
        <img src="https://images.pexels.com/photos/1527934/pexels-photo-1527934.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1">
        <div class="floater">
            <h4></h4>
            <h1></h1>
            <p><a class="button" href="">MORE</a></p>
        </div>
    </div>
    <div class="section front-section-b">
        <img src="https://science.nasa.gov/wp-content/uploads/2023/09/web-first-images-release.png?w=2048&format=webp">
        <div class="floater">
            <h4></h4>
            <h1></h1>
            <p><a class="button" href="">MORE</a></p>
        </div>
    </div>
</main>
<footer>
    <div class="floater">
        <div>
            <h1>About</h1>

            Jingga was founded in 2023 by Dennis Eichhorn and provides smart business solutions for every company size. Managing and performing daily business tasks has naver been easier.

            Our software is user friendly, performant, affordable and versatile.
        </div>
        <div>
            <h1>Social</h1>
            <ul>
                <li><a href="">Twitter</a></li>
                <li><a href="">Facebook</a></li>
                <li><a href="">Instagram</a></li>
                <li><a href="">YouTube</a></li>
                <li><a href="">Donate</a></li>
                <li><a href="">Patreon</a></li>
            </ul>
        </div>

        <div>
            <h1>Other</h1>
            <ul>
                <li><a href="">Feedback</a></li>
                <li><a href="">Contact</a></li>
            </ul>
        </div>

        <div>
            <h1>Legal</h1>
            <ul>
                <li><a href="">Imprint</a></li>
                <li><a href="">Terms of Service</a></li>
                <li><a href="">Privacy</a></li>
            </ul>
        </div>
    </div>
</footer>
<?= $head->renderAssetsLate(); ?>
