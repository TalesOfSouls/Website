<?php

use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^/*$' => [
        [
            'dest' => '\Controller\FrontendController:frontView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/signup' => [
        [
            'dest' => '\Controller\FrontendController:signupView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/signup/success' => [
        [
            'dest' => '\Controller\FrontendController:signupSuccessView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/signup/confirmation.*' => [
        [
            'dest' => '\Controller\FrontendController:signupConfirmationView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/signin' => [
        [
            'dest' => '\Controller\FrontendController:signinView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/forgot' => [
        [
            'dest' => '\Controller\FrontendController:forgotView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/news' => [
        [
            'dest' => '\Controller\FrontendController:newsView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/shop' => [
        [
            'dest' => '\Controller\FrontendController:shopView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/shop/item.*' => [
        [
            'dest' => '\Controller\FrontendController:shopItemView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/shop/buy/oneclick.*' => [
        [
            'dest'       => '\Controller\FrontendController:apiOneClickBuy',
            'verb'       => RouteVerb::GET,
            'active'     => true,
        ],
    ],
    '^/shop/buy/success.*' => [
        [
            'dest' => '\Controller\FrontendController:shopBuySuccessView',
            'verb' => RouteVerb::GET,
            /* @todo not working because after the redirect the session is lost */
            /*
            'active' => true,
            'permission' => [
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PROFILE,
            ],
            */
        ],
    ],
    '^/contact' => [
        [
            'dest' => '\Controller\FrontendController:contactView',
            'verb' => RouteVerb::GET | RouteVerb::SET,
        ],
    ],
    '^/imprint' => [
        [
            'dest' => '\Controller\FrontendController:imprintView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/privacy' => [
        [
            'dest' => '\Controller\FrontendController:privacyView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/terms' => [
        [
            'dest' => '\Controller\FrontendController:termsView',
            'verb' => RouteVerb::GET,
        ],
    ],
    '^/dpa' => [
        [
            'dest' => '\Controller\FrontendController:dpaView',
            'verb' => RouteVerb::GET,
        ],
    ],
];