<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Application\Frontend
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Application\Frontend;

use Models\CoreSettings;
use phpOMS\Account\Account;
use phpOMS\Account\AccountManager;
use phpOMS\Account\NullAccount;
use phpOMS\Asset\AssetType;
use phpOMS\Auth\Auth;
use phpOMS\DataStorage\Cache\CachePool;
use phpOMS\DataStorage\Cookie\CookieJar;
use phpOMS\DataStorage\Database\DatabasePool;
use phpOMS\DataStorage\Database\DatabaseStatus;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\DataStorage\Session\HttpSession;
use phpOMS\Dispatcher\Dispatcher;
use phpOMS\Event\EventManager;
use phpOMS\Localization\L11nManager;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Model\Html\Head;
use phpOMS\Module\ModuleManager;
use phpOMS\Router\RouteStatus;
use phpOMS\Router\WebRouter;
use phpOMS\Uri\UriFactory;
use phpOMS\Utils\Parser\Markdown\Markdown;
use Application\WebApplication;
use Application\Frontend\AppView;

/**
 * Application class.
 *
 * @package Web\Frontend
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
final class Application
{
    /**
     * WebApplication.
     *
     * @var WebApplication
     * @since 1.0.0
     */
    private WebApplication $app;

    /**
     * Temp config.
     *
     * @var array{db:array{core:array{masters:array{select:array{db:string, host:string, port:int, login:string, password:string, database:string}}}}, log:array{file:array{path:string}}, app:array{path:string, default:array{id:string, app:string, org:int, lang:string}, domains:array}, page:array{root:string, https:bool}, language:string[]}
     * @since 1.0.0
     */
    private array $config;

    /**
     * Constructor.
     *
     * @param WebApplication                                                                                                                                                                                                                                                                                                                            $app    WebApplication
     * @param array{db:array{core:array{masters:array{select:array{db:string, host:string, port:int, login:string, password:string, database:string}}}}, log:array{file:array{path:string}}, app:array{path:string, default:array{id:string, app:string, org:int, lang:string}, domains:array}, page:array{root:string, https:bool}, language:string[]} $config Application config
     *
     * @since 1.0.0
     */
    public function __construct(WebApplication $app, array $config)
    {
        $this->app          = $app;
        $this->app->appName = 'Frontend';
        $this->config       = $config;
        UriFactory::setQuery('/app', \strtolower($this->app->appName));
    }

    public function run(HttpRequest $request, HttpResponse $response) : void
    {
        $this->app->l11nManager    = new L11nManager();
        $this->app->dbPool         = new DatabasePool();
        $this->app->sessionManager = new HttpSession(0);
        $this->app->cookieJar      = new CookieJar();
        $this->app->dispatcher     = new Dispatcher($this->app);

        $this->app->dbPool->create('select', $this->config['db']);

        $this->app->router = new WebRouter($this->app);
        $this->app->router->importFromFile(__DIR__ . '/Routes.php');

        /* CSRF token OK? */
        if ($request->hasData('CSRF')
            && !\hash_equals($this->app->sessionManager->data['CSRF'] ?? '', $request->getDataString('CSRF'))
        ) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        /** @var \phpOMS\DataStorage\Database\Connection\ConnectionAbstract $con */
        $con = $this->app->dbPool->get();
        DataMapperFactory::db($con);

        $this->app->appId = 1;

        $this->app->cachePool = new CachePool();
        foreach (($this->config['cache'] ?? []) as $name => $cache) {
            $this->app->cachePool->create($name, $cache);
        }

        $this->app->appSettings    = new CoreSettings($this->app->cachePool->get());
        $this->app->eventManager   = new EventManager($this->app->dispatcher);
        $this->app->accountManager = new AccountManager($this->app->sessionManager);
        $this->app->unitId         = 1;

        $aid                       = Auth::authenticate($this->app->sessionManager);
        $account                   = $this->loadAccount($aid);
        $request->header->account  = $account->id;
        $response->header->account = $account->id;

        if ($account->id > 0) {
            $response->header->l11n = $account->l11n;
        } elseif (isset($this->app->sessionManager->data['language'])
            && $response->header->l11n->language !== $this->app->sessionManager->data['language']
        ) {
            $response->header->l11n
                ->loadFromLanguage(
                    $this->app->sessionManager->data['language'],
                    $this->app->sessionManager->data['country'] ?? '*'
                );
        } else {
            $this->app->setResponseLanguage($request, $response, $this->config);
        }

        if (!\in_array($response->header->l11n->language, $this->config['language'])) {
            $response->header->l11n->language = $this->app->l11nServer->language;
        }

        $pageView = new AppView($this->app->l11nManager, $request, $response);
        $head     = new Head();

        $pageView->head = $head;
        $response->set('Content', $pageView);

        /* Database OK? */
        if ($this->app->dbPool->get()->getStatus() !== DatabaseStatus::OK) {
            $this->create503Response($response, $pageView);

            return;
        }

        UriFactory::setQuery('/lang', $response->header->l11n->language);

        $this->app->loadLanguageFromPath(
            $response->header->l11n->language,
            __DIR__ . '/lang/' . $response->header->l11n->language . '.lang.php'
        );

        $response->header->set('content-language', $response->header->l11n->language, true);

        $dispatched = $this->routeDispatching($request, $response, $account, $head, $pageView);
        $pageView->addData('dispatch', $dispatched);
    }

    private function routeDispatching(
        HttpRequest $request,
        HttpResponse $response,
        Account $account,
        Head $head,
        AppView $pageView
    ) : array
    {
        $routes = $this->app->router->route(
            $request->uri->getRoute(),
            $request->getDataString('CSRF'),
            $request->getRouteVerb(),
            $this->app->appId,
            $this->app->unitId,
            $account,
            $request->data
        );

        if ($routes === ['dest' => RouteStatus::INVALID_CSRF]
            || $routes === ['dest' => RouteStatus::INVALID_PERMISSIONS]
            || $routes === ['dest' => RouteStatus::INVALID_DATA]
        ) {
            $this->initResponseHeadFrontend($head, $request, $response);
            $pageView->setTemplate('/Application/Frontend/tpl/general');

            return $this->app->dispatcher->dispatch(
                $this->app->router->route(
                    '/' . \strtolower($this->app->appName) . '/e403',
                    $request->getDataString('CSRF'),
                    $request->getRouteVerb()
                ),
                $request, $response);
        } elseif ($routes === ['dest' => RouteStatus::NOT_LOGGED_IN]) {
            $this->initResponseHeadFrontend($head, $request, $response);
            $pageView->setTemplate('/Application/Frontend/tpl/general');

            return $this->app->dispatcher->dispatch(
                $this->app->router->route(
                    '/' . \strtolower($this->app->appName) . '/signup',
                    $request->getDataString('CSRF'),
                    $request->getRouteVerb()
                ),
                $request, $response);
        } else {
            $this->initResponseHeadFrontend($head, $request, $response);

            if (isset($routes[0]['dest'])
                && \stripos($routes[0]['dest'], '\Controller\FrontendController:front') !== false
            ) {
                $pageView->setTemplate('/Application/Frontend/tpl/front');
            } elseif (isset($routes[0]['dest'])
                && \stripos($routes[0]['dest'], '\Controller\FrontendController:shopView') !== false
            ) {
                $pageView->setTemplate('/Application/Frontend/tpl/shop-front');
            } else {
                $pageView->setTemplate('/Application/Frontend/tpl/general');
            }

            $pageView->setData('short_about', Markdown::parse(''));

            return $this->app->dispatcher->dispatch($routes, $request, $response);
        }
    }

    private function create406Response(HttpResponse $response, AppView $pageView) : void
    {
        $response->header->status = RequestStatusCode::R_406;
        $pageView->setTemplate('/Application/Frontend/error/406');
        $this->app->loadLanguageFromPath(
            $response->header->l11n->language,
            __DIR__ . '/error/lang/' . $response->header->l11n->language . '.lang.php'
        );
    }

    private function create503Response(HttpResponse $response, AppView $pageView) : void
    {
        $response->header->status = RequestStatusCode::R_503;
        $pageView->setTemplate('/Application/Frontend/error/503');
        $this->app->loadLanguageFromPath(
            $response->header->l11n->language,
            __DIR__ . '/error/lang/' . $response->header->l11n->language . '.lang.php'
        );
    }

    private function loadAccount(int $uid) : Account
    {
        return new NullAccount();

        /** @var Account $account */
        $account = AccountMapper::getWithPermissions($uid);

        if ($account instanceof ModelsNullAccount) {
            $account = new NullAccount();
        }

        $this->app->accountManager->add($account);

        return $account;
    }

    private function initResponseHeadFrontend(Head $head, HttpRequest $request, HttpResponse $response) : void
    {
        $scriptSrc = \bin2hex(\random_bytes(32));
        $this->app->appSettings->setOption('script-nonce', $scriptSrc);

        $response->header->set('content-security-policy',
            'base-uri \'self\';'
            . 'object-src \'none\';'
            . 'script-src \'nonce-' . $scriptSrc . '\' \'strict-dynamic\';'
            . 'worker-src \'self\';'
        );

        /*
        $response->header->set('content-security-policy',
            'base-uri \'self\';'
            . 'object-src \'none\';'
            . 'script-src \'nonce-' . $scriptSrc . '\' \'strict-dynamic\' https: \'self\''
                . ' blob: \'sha256-' . \base64_encode(\hash('sha256', $script, true)) . '\';'
            . 'worker-src \'self\';'
        );
        */

        /* Load assets */
        $head->addAsset(AssetType::CSS, 'Resources/fonts/googleicons/styles.css', ['defer']);
        $head->addAsset(AssetType::CSS, 'Application/Frontend/css/frontend.css?v=1.0.0', ['defer']);

        // Framework
        $head->addAsset(AssetType::JS, 'Application/Frontend/js/frontend.min.js?v=1.0.0', ['nonce' => $scriptSrc, 'type' => 'module', 'defer']);

        if ($request->hasData('debug')) {
            $head->addAsset(AssetType::CSS, 'cssOMS/debug.css?v=1.0.0');
            \phpOMS\DataStorage\Database\Query\Builder::$log = true;
        }

        $css = \file_get_contents(__DIR__ . '/css/frontend-small.css');
        if ($css === false) {
            $css = '';
        }

        $css = \preg_replace('!\s+!', ' ', $css);
        $head->setStyle('core', $css ?? '');
        $head->title = 'Jingga';
    }
}
