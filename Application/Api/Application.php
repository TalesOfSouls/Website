<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Application\Api
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Application\Api;

use Application\WebApplication;
use Models\AccountMapper;
use Models\AccountStatus;
use Models\CoreSettings;
use phpOMS\Account\Account;
use phpOMS\Account\AccountManager;
use phpOMS\Account\NullAccount;
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
use phpOMS\Message\NotificationLevel;
use phpOMS\Router\RouteStatus;
use phpOMS\Router\WebRouter;
use phpOMS\System\MimeType;
use phpOMS\Uri\UriFactory;

/**
 * Api application.
 *
 * Receives the data from the website forms (sign in, feedback, votes, ...).
 *
 * Requests from jsOMS (XMLHttpRequest) get a JSON response:
 *     [{"status": "ok", "title": "", "message": "...", "response": {...}}]
 * or an action for the response manager of jsOMS (see Application/Frontend/js):
 *     [{"type": "redirect", "uri": "/feedback/9"}]
 *
 * Normal form posts (no JavaScript) are redirected back to the website and the message is shown there.
 *
 * @package Application\Api
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
     * @var array
     * @since 1.0.0
     */
    private array $config;

    /**
     * Constructor.
     *
     * @param WebApplication $app    WebApplication
     * @param array          $config Application config
     *
     * @since 1.0.0
     */
    public function __construct(WebApplication $app, array $config)
    {
        $this->app          = $app;
        $this->app->appName = 'Api';
        $this->config       = $config;
        UriFactory::setQuery('/app', 'api');
    }

    /**
     * Handle the api request
     *
     * @param HttpRequest  $request  Request
     * @param HttpResponse $response Response
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function run(HttpRequest $request, HttpResponse $response) : void
    {
        $response->header->set('Content-Type', MimeType::M_JSON . '; charset=utf-8', true);

        $this->app->l11nManager    = new L11nManager();
        $this->app->dbPool         = new DatabasePool();
        $this->app->sessionManager = new HttpSession(0);
        $this->app->cookieJar      = new CookieJar();
        $this->app->dispatcher     = new Dispatcher($this->app);

        $this->app->dbPool->create('select', $this->config['db']);

        $this->app->router = new WebRouter($this->app);
        $this->app->router->importFromFile(__DIR__ . '/Routes.php');

        /* CSRF token OK? (if a route requires one is defined in the routes) */
        if ($request->hasData('CSRF')
            && !\hash_equals($this->app->sessionManager->data['CSRF'] ?? '', $request->getDataString('CSRF') ?? '')
        ) {
            $this->error($request, $response, RequestStatusCode::R_403, 'Your session expired. Please reload the page and try again.');

            return;
        }

        /* Database OK? */
        if ($this->app->dbPool->get()->getStatus() !== DatabaseStatus::OK) {
            $this->error($request, $response, RequestStatusCode::R_503, 'The service is currently not available.');

            return;
        }

        /** @var \phpOMS\DataStorage\Database\Connection\ConnectionAbstract $con */
        $con = $this->app->dbPool->get();
        DataMapperFactory::db($con);

        $this->app->appId  = 2;
        $this->app->unitId = 1;

        $this->app->cachePool      = new CachePool();
        $this->app->appSettings    = new CoreSettings($this->app->cachePool->get());
        $this->app->eventManager   = new EventManager($this->app->dispatcher);
        $this->app->accountManager = new AccountManager($this->app->sessionManager);

        $account                   = $this->loadAccount(Auth::authenticate($this->app->sessionManager));
        $request->header->account  = $account->id;
        $response->header->account = $account->id;

        $this->app->loadLanguageFromPath(
            $response->header->l11n->language,
            __DIR__ . '/lang/' . $response->header->l11n->language . '.lang.php'
        );

        $routes = $this->app->router->route(
            $request->uri->getRoute(),
            $request->getDataString('CSRF'),
            $request->getRouteVerb(),
            $this->app->appId,
            $this->app->unitId,
            $account,
            $request->data
        );

        if ($routes === ['dest' => RouteStatus::INVALID_CSRF]) {
            $this->error($request, $response, RequestStatusCode::R_403, 'Your session expired. Please reload the page and try again.');

            return;
        } elseif ($routes === ['dest' => RouteStatus::INVALID_PERMISSIONS]
            || $routes === ['dest' => RouteStatus::INVALID_DATA]
        ) {
            $this->error($request, $response, RequestStatusCode::R_403, 'You are not allowed to do this.');

            return;
        } elseif ($routes === ['dest' => RouteStatus::NOT_LOGGED_IN]) {
            $this->error($request, $response, RequestStatusCode::R_403, 'Please sign in first.');

            return;
        }

        $dispatched = $this->app->dispatcher->dispatch($routes, $request, $response);

        if (empty($dispatched) && empty($response->data)) {
            $this->error($request, $response, RequestStatusCode::R_404, 'The endpoint you requested doesn\'t exist.');
        }
    }

    /**
     * Error response
     *
     * @param HttpRequest  $request  Request
     * @param HttpResponse $response Response
     * @param int          $status   Http status code
     * @param string       $message  Message
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function error(HttpRequest $request, HttpResponse $response, int $status, string $message) : void
    {
        $response->header->status = $status;
        $response->set($request->uri->__toString(), [
            'status'   => NotificationLevel::ERROR,
            'title'    => 'Error',
            'message'  => $message,
            'response' => [],
        ]);
    }

    /**
     * Load the signed in account
     *
     * @param int $uid Account id from the session
     *
     * @return Account
     *
     * @since 1.0.0
     */
    private function loadAccount(int $uid) : Account
    {
        if ($uid < 1) {
            return new NullAccount();
        }

        /** @var \Models\Account $model */
        $model = AccountMapper::get()->where('id', $uid)->execute();

        if ($model->id === 0 || $model->status !== AccountStatus::ACTIVE) {
            Auth::logout($this->app->sessionManager);

            return new NullAccount();
        }

        $account        = new Account($model->id);
        $account->login = $model->name;
        $account->name1 = $model->name;
        $account->email = $model->email;

        $this->app->accountManager->add($account);

        return $account;
    }
}
