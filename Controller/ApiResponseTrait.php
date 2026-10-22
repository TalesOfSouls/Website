<?php
/**
 * Tales of Souls
 *
 * PHP Version 8.2
 *
 * @package   Controller
 * @version   1.0.0
 */
declare(strict_types=1);

namespace Controller;

use Models\Account;
use Models\AccountMapper;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\MimeType;

/**
 * Response helpers of the api controllers
 *
 * Every endpoint answers in two ways:
 *  - jsOMS requests (XMLHttpRequest) receive JSON, either a notification
 *    {"status", "title", "message", "response"} or an action for the jsOMS response manager {"type", ...}
 *  - normal form posts (browser without JavaScript) are redirected (303) back to the website,
 *    the message is stored in the session and shown on the next page (see tpl/header.php)
 *
 * @package Controller
 * @since   1.0.0
 */
trait ApiResponseTrait
{
    /**
     * Did the browser send a normal form post (no JavaScript)?
     *
     * jsOMS sends XMLHttpRequests which don't ask for text/html.
     *
     * @param RequestAbstract $request Request
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function wantsHtml(RequestAbstract $request) : bool
    {
        foreach ($request->header->get('accept') as $accept) {
            if (\stripos($accept, 'text/html') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Local redirect target of the request (no external redirects)
     *
     * @param RequestAbstract $request  Request
     * @param string          $fallback Fallback url
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function redirectTarget(RequestAbstract $request, string $fallback = '/') : string
    {
        $target = $request->getDataString('redirect') ?? '';

        if ($target === '') {
            $referer = $request->header->get('referer')[0] ?? '';
            $parts   = \parse_url($referer);

            if ($parts !== false && isset($parts['path']) && ($parts['host'] ?? '') === ($request->uri->host ?? '')) {
                $target = $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');
            }
        }

        // only paths on this website, no "//evil.com" or "/\evil.com"
        if ($target === '' || $target[0] !== '/' || \str_starts_with($target, '//') || \str_contains($target, '\\')) {
            return $fallback;
        }

        return $target;
    }

    /**
     * Respond to the request
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param string           $status   Notification level (see NotificationLevel)
     * @param string           $message  Message for the user
     * @param array            $data     Response data (for jsOMS)
     * @param null|string      $redirect Redirect after a normal form post (null = back to the form)
     * @param null|string      $type     jsOMS response action (e.g. redirect, vote, comment)
     * @param int              $code     Http status code
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function respond(
        RequestAbstract $request,
        ResponseAbstract $response,
        string $status,
        string $message,
        array $data = [],
        ?string $redirect = null,
        ?string $type = null,
        int $code = RequestStatusCode::R_200
    ) : void
    {
        if ($this->wantsHtml($request)) {
            $this->app->sessionManager->set('flash', ['status' => $status, 'title' => '', 'message' => $message], true);

            $response->header->set('Content-Type', MimeType::M_HTML . '; charset=utf-8', true);
            $response->header->set('Location', $redirect ?? $this->redirectTarget($request), true);
            $response->header->status = RequestStatusCode::R_303;

            return;
        }

        $response->header->status = $code;
        $response->header->set('Content-Type', MimeType::M_JSON . '; charset=utf-8', true);

        if ($type !== null) {
            // the message is shown on the next page (e.g. after a redirect)
            if ($type === 'redirect' && $message !== '') {
                $this->app->sessionManager->set('flash', ['status' => $status, 'title' => '', 'message' => $message], true);
            }

            $response->data[$request->uri->__toString()] = ['type' => $type, 'status' => $status, 'message' => $message] + $data;

            return;
        }

        $response->data[$request->uri->__toString()] = [
            'status'   => $status,
            'title'    => '',
            'message'  => $message,
            'response' => $data,
        ];
    }

    /**
     * Respond with invalid input
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param string           $message  Message
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function invalid(RequestAbstract $request, ResponseAbstract $response, string $message) : void
    {
        $this->respond($request, $response, NotificationLevel::WARNING, $message, [], null, null, RequestStatusCode::R_400);
    }

    /**
     * Make sure the user is signed in
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function requireAccount(RequestAbstract $request, ResponseAbstract $response) : bool
    {
        if ($request->header->account > 0) {
            return true;
        }

        $back = $this->redirectTarget($request);
        $this->respond(
            $request, $response, NotificationLevel::INFO, 'Please sign in first.',
            ['uri' => '/signin?redirect=' . \urlencode($back), 'delay' => 1500],
            '/signin?redirect=' . \urlencode($back),
            'redirect',
            RequestStatusCode::R_403
        );

        return false;
    }

    /**
     * Signed in account (website model)
     *
     * @param RequestAbstract $request Request
     *
     * @return Account
     *
     * @since 1.0.0
     */
    protected function currentAccount(RequestAbstract $request) : Account
    {
        /** @var Account $account */
        $account = AccountMapper::get()->where('id', $request->header->account)->execute();

        return $account;
    }

    /**
     * Make sure the user is signed in and has the role
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param bool             $admin    Administrators only (otherwise developers are allowed as well)
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function requireRole(RequestAbstract $request, ResponseAbstract $response, bool $admin = true) : bool
    {
        if (!$this->requireAccount($request, $response)) {
            return false;
        }

        $account = $this->currentAccount($request);
        if ($admin ? $account->isAdmin() : $account->isDeveloper()) {
            return true;
        }

        $this->respond($request, $response, NotificationLevel::ERROR, 'You are not allowed to do this.', [], null, null, RequestStatusCode::R_403);

        return false;
    }
}
