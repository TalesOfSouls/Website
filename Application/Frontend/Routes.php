<?php
/**
 * Frontend routes
 *
 * Every pattern is anchored at the end with an optional query string because the router
 * matches against the route including the query (e.g. "/faq?category=Game").
 * Without the end anchor "/feedback" would also match "/feedback/create" and both views
 * would be rendered on the same page.
 */
declare(strict_types=1);

use phpOMS\Router\RouteVerb;

/**
 * Short helper for simple GET pages handled by the FrontendController.
 *
 * @param string $action Controller method name
 * @param int    $verb   Route verb
 *
 * @return array
 */
$page = static fn (string $action, int $verb = RouteVerb::GET) : array => [
    [
        'dest' => '\Controller\FrontendController:' . $action,
        'verb' => $verb,
    ],
];

// optional query string + end of route
$q = '(\?.*)?$';

return [
    // Home
    '^/*' . $q => $page('frontView'),

    // Account
    '^/signup' . $q                   => $page('signupView', RouteVerb::GET | RouteVerb::SET),
    '^/signup/success' . $q           => $page('signupSuccessView'),
    '^/signup/confirmation' . $q      => $page('signupConfirmationView'),
    '^/signin' . $q                   => $page('signinView', RouteVerb::GET | RouteVerb::SET),
    '^/forgot' . $q                   => $page('forgotView', RouteVerb::GET | RouteVerb::SET),

    // News
    '^/news' . $q                     => $page('newsView'),
    '^/news/[a-z0-9_\-]+' . $q        => $page('newsArticleView'),

    // Game hub
    '^/game' . $q                     => $page('gameView'),
    '^/faq' . $q                      => $page('faqView'),
    '^/wiki(/.*)?$'                   => $page('wikiView'),
    '^/map' . $q                      => $page('mapView'),
    '^/craft' . $q                    => $page('craftView'),
    '^/videos' . $q                   => $page('videosView'),
    '^/development' . $q              => $page('developmentView'),
    '^/patreon' . $q                  => $page('supportView'),
    '^/support' . $q                  => $page('supportView'),

    // Profiles
    '^/player/profile' . $q                   => $page('profilePlayerListView'),
    '^/player/profile/\d+' . $q               => $page('profilePlayerView'),
    '^/player/profile/\d+/(dungeon|raid|worldboss|pvp)/\d+' . $q => $page('profileEncounterView'),
    '^/player/character/profile' . $q         => $page('profileCharacterListView'),
    '^/player/character/profile/\d+' . $q     => $page('profileCharacterView'),
    '^/guild/profile' . $q                    => $page('profileGuildListView'),
    '^/guild/profile/\d+' . $q                => $page('profileGuildView'),
    '^/pet/profile' . $q                      => $page('petProfileView'),
    '^/profile/town' . $q                     => $page('townView'),

    // Pets
    '^/pet/battle' . $q               => $page('petBattleView'),
    '^/pet/daycare' . $q              => $page('petDaycareView'),

    // Database
    '^/database' . $q                 => $page('databaseView'),
    '^/database/item/\d+' . $q        => $page('databaseItemView'),
    '^/database/skill/\d+' . $q       => $page('databaseSkillView'),

    // Competitive & community
    '^/ladder' . $q                   => $page('ladderView'),
    '^/build/list' . $q               => $page('buildListView'),
    '^/build/planner' . $q            => $page('buildPlannerView'),
    '^/auction(/[a-z_]+)?' . $q       => $page('auctionHouseView'),
    '^/recordings' . $q               => $page('recordingListView'),
    '^/feedback' . $q                 => $page('feedbackListView'),
    '^/feedback/create' . $q          => $page('feedbackCreateView', RouteVerb::GET | RouteVerb::SET),
    '^/feedback/\d+' . $q              => $page('feedbackView', RouteVerb::GET | RouteVerb::SET),

    // Shop
    '^/shop' . $q                     => $page('shopView'),
    '^/shop/item/[a-z0-9_\-]+' . $q   => $page('shopItemView'),
    '^/shop/buy/oneclick.*' => [
        [
            'dest'   => '\Controller\FrontendController:apiOneClickBuy',
            'verb'   => RouteVerb::GET,
            'active' => true,
        ],
    ],
    '^/shop/buy/success' . $q         => $page('shopBuySuccessView'),

    // Contact & legal
    '^/contact' . $q                  => $page('contactView', RouteVerb::GET | RouteVerb::SET),
    '^/imprint' . $q                  => $page('imprintView'),
    '^/privacy' . $q                  => $page('privacyView'),
    '^/terms' . $q                    => $page('termsView'),
    '^/dpa' . $q                      => $page('dpaView'),
    '^/conduct' . $q                  => $page('cocView'),
    '^/cla' . $q                      => $page('claView'),

    // Errors (used by Application::routeDispatching)
    '^/frontend/e403' . $q            => $page('error403View'),
    '^/frontend/signup' . $q          => $page('signinView'),
];
