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
use Models\AccountRole;
use Models\AccountStatus;
use Models\ContactMessageMapper;
use Models\DevelopmentAreaMapper;
use Models\Faq;
use Models\FaqMapper;
use Models\FeedbackMapper;
use Models\MilestoneMapper;
use Models\News;
use Models\NewsMapper;
use Models\WikiPage;
use Models\WikiPageMapper;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Module\ModuleAbstract;
use phpOMS\Views\View;

/**
 * Administration pages (/admin)
 *
 * The pages only show the forms, the data is saved by the AdminApiController (/api/admin/...).
 *
 * @package Controller
 * @since   1.0.0
 */
final class BackendController extends ModuleAbstract
{
    /**
     * Providing.
     *
     * @var string[]
     * @since 1.0.0
     */
    public static array $providing = [];

    /**
     * Dependencies.
     *
     * @var string[]
     * @since 1.0.0
     */
    public static array $dependencies = [];

    /**
     * Entries per page
     *
     * @var int
     * @since 1.0.0
     */
    private const PER_PAGE = 50;

    /**
     * Create an admin view (or the sign in redirect / access denied page)
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param string           $template Template name (relative to Application/Frontend/tpl/admin)
     * @param string           $title    Page title
     * @param string           $section  Active admin section (navigation)
     *
     * @return View
     */
    private function createAdminView(RequestAbstract $request, ResponseAbstract $response, string $template, string $title, string $section) : View
    {
        $view = new View($this->app->l11nManager, $request, $response);

        /** @var Account $account */
        $account = AccountMapper::get()->where('id', $request->header->account)->execute();

        if ($account->id === 0) {
            $response->header->set('Location', '/signin?redirect=' . \urlencode('/' . \ltrim($request->uri->getRoute(), '/')), true);
            $response->header->status = RequestStatusCode::R_303;
            $view->setTemplate('/Application/Frontend/tpl/error/403');

            return $view;
        }

        if (!$account->isAdmin()) {
            $response->header->status = RequestStatusCode::R_403;
            $view->setTemplate('/Application/Frontend/tpl/error/403');

            return $view;
        }

        if (isset($response->data['Content']->head)) {
            $response->data['Content']->head->title .= ' - Admin - ' . $title;
        }

        $view->setTemplate('/Application/Frontend/tpl/admin/' . $template);
        $view->data['section'] = $section;
        $view->data['title']   = $title;
        $view->data['admin']   = $account;
        $view->data['allowed'] = true;

        return $view;
    }

    /**
     * Is the view an admin page (and not the access denied page)?
     *
     * @param View $view View
     *
     * @return bool
     */
    private function allowed(View $view) : bool
    {
        return $view->data['allowed'] ?? false;
    }

    /* ======================================================================
       Dashboard
       ====================================================================== */

    public function adminDashboardView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = $this->createAdminView($request, $response, 'dashboard', 'Dashboard', 'dashboard');
        if (!$this->allowed($view)) {
            return $view;
        }

        $view->data['counts'] = [
            'Accounts'      => AccountMapper::count()->executeCount(),
            'News posts'    => NewsMapper::count()->executeCount(),
            'Wiki articles' => WikiPageMapper::count()->executeCount(),
            'FAQ entries'   => FaqMapper::count()->executeCount(),
            'Feedback'      => FeedbackMapper::count()->executeCount(),
            'Messages'      => ContactMessageMapper::count()->executeCount(),
        ];

        $view->data['feedback'] = FeedbackMapper::getAll()->with('account')->sort('created', 'DESC')->limit(8)->executeGetArray();
        $view->data['messages'] = ContactMessageMapper::getAll()->sort('created', 'DESC')->limit(5)->executeGetArray();
        $view->data['accounts'] = AccountMapper::getAll()->where('created', null, '!=')->sort('created', 'DESC')->limit(5)->executeGetArray();

        return $view;
    }

    /* ======================================================================
       News
       ====================================================================== */

    public function adminNewsListView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = $this->createAdminView($request, $response, 'news_list', 'News', 'news');
        if ($this->allowed($view)) {
            $view->data['news'] = NewsMapper::getAll()->with('account')->sort('published', 'DESC')->executeGetArray();
        }

        return $view;
    }

    public function adminNewsEditView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $id   = (int) $request->uri->getPathElement(2);
        $view = $this->createAdminView($request, $response, 'news_edit', $id > 0 ? 'Edit news post' : 'New news post', 'news');
        if (!$this->allowed($view)) {
            return $view;
        }

        /** @var News $news */
        $news = $id > 0 ? NewsMapper::get()->with('account')->where('id', $id)->execute() : new News();
        if ($id > 0 && $news->id === 0) {
            $response->header->status = RequestStatusCode::R_404;
            $view->setTemplate('/Application/Frontend/tpl/error/404');

            return $view;
        }

        $images = [];
        foreach (\glob(__DIR__ . '/../Application/Frontend/{data/news/img/*/*,img/*}', \GLOB_BRACE) ?: [] as $file) {
            if (\preg_match('/\.(png|jpe?g|webp|gif)$/i', $file) === 1) {
                $images[] = '/' . \ltrim(\str_replace(\realpath(__DIR__ . '/..'), '', (string) \realpath($file)), '/');
            }
        }

        $view->data['news']   = $news;
        $view->data['images'] = $images;

        return $view;
    }

    /* ======================================================================
       Wiki
       ====================================================================== */

    public function adminWikiListView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = $this->createAdminView($request, $response, 'wiki_list', 'Wiki', 'wiki');
        if ($this->allowed($view)) {
            $pages = WikiPageMapper::getAll()->with('account')->executeGetArray();
            \usort($pages, fn ($a, $b) => [$a->navOrder === null, $a->navOrder, $a->title] <=> [$b->navOrder === null, $b->navOrder, $b->title]);

            $view->data['pages'] = $pages;
        }

        return $view;
    }

    public function adminWikiEditView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $id   = (int) $request->uri->getPathElement(2);
        $view = $this->createAdminView($request, $response, 'wiki_edit', $id > 0 ? 'Edit article' : 'New article', 'wiki');
        if (!$this->allowed($view)) {
            return $view;
        }

        /** @var WikiPage $page */
        $page = $id > 0 ? WikiPageMapper::get()->where('id', $id)->execute() : new WikiPage();
        if ($id > 0 && $page->id === 0) {
            $response->header->status = RequestStatusCode::R_404;
            $view->setTemplate('/Application/Frontend/tpl/error/404');

            return $view;
        }

        if ($page->id === 0) {
            $page->slug = \strtolower(\trim($request->getDataString('slug') ?? ''));
        }

        $view->data['page']    = $page;
        $view->data['modules'] = AdminApiController::wikiModules();

        return $view;
    }

    /* ======================================================================
       FAQ
       ====================================================================== */

    public function adminFaqListView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = $this->createAdminView($request, $response, 'faq_list', 'FAQ', 'faq');
        if ($this->allowed($view)) {
            $faqs = FaqMapper::getAll()->executeGetArray();
            \usort($faqs, fn ($a, $b) => [$a->category, $a->order, $a->id] <=> [$b->category, $b->order, $b->id]);

            $view->data['faqs'] = $faqs;
        }

        return $view;
    }

    public function adminFaqEditView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $id   = (int) $request->uri->getPathElement(2);
        $view = $this->createAdminView($request, $response, 'faq_edit', $id > 0 ? 'Edit question' : 'New question', 'faq');
        if (!$this->allowed($view)) {
            return $view;
        }

        /** @var Faq $faq */
        $faq = $id > 0 ? FaqMapper::get()->where('id', $id)->execute() : new Faq();
        if ($id > 0 && $faq->id === 0) {
            $response->header->status = RequestStatusCode::R_404;
            $view->setTemplate('/Application/Frontend/tpl/error/404');

            return $view;
        }

        $categories = [];
        foreach (FaqMapper::getAll()->executeGetArray() as $entry) {
            $categories[$entry->category] = $entry->category;
        }

        \ksort($categories);

        $view->data['faq']        = $faq;
        $view->data['categories'] = $categories;

        return $view;
    }

    /* ======================================================================
       Development
       ====================================================================== */

    public function adminDevelopmentView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = $this->createAdminView($request, $response, 'development', 'Development', 'development');
        if ($this->allowed($view)) {
            $view->data['milestones'] = MilestoneMapper::getAll()->sort('order', 'ASC')->executeGetArray();
            $view->data['areas']      = DevelopmentAreaMapper::getAll()->sort('order', 'ASC')->executeGetArray();
        }

        return $view;
    }

    /* ======================================================================
       Accounts & messages
       ====================================================================== */

    public function adminUserListView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = $this->createAdminView($request, $response, 'users', 'Accounts', 'users');
        if (!$this->allowed($view)) {
            return $view;
        }

        $search = \trim($request->getDataString('q') ?? '');
        $role   = $request->getDataString('role') ?? '';
        $page   = \max(1, $request->getDataInt('page') ?? 1);

        $mapper = AccountMapper::getAll();
        $count  = AccountMapper::count();

        if ($search !== '') {
            $mapper->where('name', '%' . $search . '%', 'LIKE');
            $count->where('name', '%' . $search . '%', 'LIKE');
        }

        if ($role !== '' && AccountRole::isValidValue((int) $role)) {
            $mapper->where('role', (int) $role);
            $count->where('role', (int) $role);
        }

        $view->data['accounts'] = $mapper->sort('id', 'ASC')->limit(self::PER_PAGE)->offset(($page - 1) * self::PER_PAGE)->executeGetArray();
        $view->data['search']   = $search;
        $view->data['role']     = $role;
        $view->data['page']     = $page;
        $view->data['pages']    = \max(1, (int) \ceil($count->executeCount() / self::PER_PAGE));
        $view->data['roles']    = [AccountRole::PLAYER => 'Player', AccountRole::DEVELOPER => 'Developer', AccountRole::ADMIN => 'Administrator'];
        $view->data['states']   = [AccountStatus::ACTIVE => 'Active', AccountStatus::INACTIVE => 'Inactive', AccountStatus::BANNED => 'Banned'];

        return $view;
    }

    public function adminMessageListView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = $this->createAdminView($request, $response, 'messages', 'Messages', 'messages');
        if ($this->allowed($view)) {
            $view->data['messages'] = ContactMessageMapper::getAll()->sort('created', 'DESC')->limit(200)->executeGetArray();
        }

        return $view;
    }
}
