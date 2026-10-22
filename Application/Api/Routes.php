<?php
/**
 * Api routes
 *
 * The routes are relative to /api (e.g. /api/feedback/vote => /feedback/vote).
 * Every pattern is anchored at the end with an optional query string because the router
 * matches against the route including the query.
 *
 * All routes that change data require the CSRF token of the session (hidden input "CSRF" in the forms).
 */
declare(strict_types=1);

use phpOMS\Router\RouteVerb;

/**
 * Short helper for the api routes handled by the ApiController.
 *
 * @param string $action Controller method name
 * @param int    $verb   Route verb
 * @param bool   $csrf   Requires a CSRF token
 *
 * @return array
 */
$api = static fn (string $action, int $verb = RouteVerb::SET, bool $csrf = true) : array => [
    [
        'dest' => '\Controller\ApiController:' . $action,
        'verb' => $verb,
        'csrf' => $csrf,
    ],
];

/**
 * Administration routes handled by the AdminApiController
 */
$admin = static fn (string $action) : array => [
    [
        'dest' => '\Controller\AdminApiController:' . $action,
        'verb' => RouteVerb::SET,
        'csrf' => true,
    ],
];

// optional query string + end of route
$q = '(\?.*)?$';

return [
    // Account
    '^/account/signin' . $q  => $api('apiAccountSignin'),
    '^/account/signup' . $q  => $api('apiAccountSignup'),
    '^/account/signout' . $q => $api('apiAccountSignout', RouteVerb::GET | RouteVerb::SET, false),
    '^/account/forgot' . $q  => $api('apiAccountForgot'),
    '^/account/update' . $q  => $api('apiAccountUpdate'),

    // Feedback board
    '^/feedback' . $q               => $api('apiFeedbackCreate'),
    '^/feedback/vote' . $q          => $api('apiFeedbackVote'),
    '^/feedback/comment' . $q       => $api('apiFeedbackCommentCreate'),
    '^/feedback/comment/vote' . $q  => $api('apiFeedbackCommentVote'),
    '^/feedback/comment/delete' . $q => $api('apiFeedbackCommentDelete'),
    '^/feedback/update' . $q        => $api('apiFeedbackUpdate'),
    '^/feedback/moderate' . $q      => $api('apiFeedbackModerate'),
    '^/feedback/delete' . $q        => $api('apiFeedbackDelete'),

    // Administration (administrators only, see AdminApiController)
    '^/admin/news' . $q             => $admin('apiNewsSave'),
    '^/admin/news/delete' . $q      => $admin('apiNewsDelete'),
    '^/admin/wiki' . $q             => $admin('apiWikiSave'),
    '^/admin/wiki/delete' . $q      => $admin('apiWikiDelete'),
    '^/admin/faq' . $q              => $admin('apiFaqSave'),
    '^/admin/faq/delete' . $q       => $admin('apiFaqDelete'),
    '^/admin/milestone' . $q        => $admin('apiMilestoneSave'),
    '^/admin/milestone/delete' . $q => $admin('apiMilestoneDelete'),
    '^/admin/area' . $q             => $admin('apiAreaSave'),
    '^/admin/area/delete' . $q      => $admin('apiAreaDelete'),
    '^/admin/account' . $q          => $admin('apiAccountRoleSave'),

    // Contact form
    '^/contact' . $q => $api('apiContact'),

    // Read only endpoints (JSON)
    '^/character/\d+' . $q => $api('apiCharacterGet', RouteVerb::GET, false),
    '^/player/\d+' . $q    => $api('apiPlayerGet', RouteVerb::GET, false),
];
