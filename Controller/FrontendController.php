<?php
declare(strict_types=1);

namespace Controller;

use Models\AccountMapper;
use Models\DevelopmentAreaMapper;
use Models\FaqMapper;
use Models\MilestoneMapper;
use Models\NewsMapper;
use Models\NewsStatus;
use Models\WikiPageMapper;
use Models\ItemMapper;
use Models\RecipeMapper;
use Application\Frontend\Provider\AchievementData;
use Application\Frontend\Provider\CharacterData;
use Application\Frontend\Provider\EncounterData;
use Application\Frontend\Provider\FeedbackData;
use Application\Frontend\Provider\LadderData;
use Application\Frontend\Provider\ProfileData;
use Application\Frontend\Provider\SeasonData;
use phpOMS\Account\PermissionType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Module\ModuleAbstract;
use phpOMS\Router\RouteVerb;
use phpOMS\Uri\UriFactory;
use phpOMS\Utils\Parser\Markdown\Markdown;
use phpOMS\Views\View;

final class FrontendController extends ModuleAbstract
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
     * Directory containing the legal markdown files
     *
     * @var string
     * @since 1.0.0
     */
    private const LEGAL_PATH = __DIR__ . '/../Application/Frontend/data/legal';

    /**
     * Fallback images for news posts without an image
     *
     * @var string[]
     * @since 1.0.0
     */
    private const NEWS_FALLBACK_IMAGES = [
        '/Application/Frontend/img/hills.jpg',
        '/Application/Frontend/img/sky.jpg',
        '/Application/Frontend/img/mountain_side.jpg',
        '/Application/Frontend/img/dusk.jpg',
    ];

    /**
     * Create a simple content view and append a title suffix to the page title
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param string           $template Template name (relative to Application/Frontend/tpl)
     * @param string           $title    Page title suffix
     *
     * @return View
     */
    private function createView(RequestAbstract $request, ResponseAbstract $response, string $template, string $title = '') : View
    {
        if ($title !== '' && isset($response->data['Content']->head)) {
            $response->data['Content']->head->title .= ' - ' . $title;
        }

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/' . $template);

        return $view;
    }

    /* ======================================================================
       Home & news
       ====================================================================== */

    public function frontView(RequestAbstract $request, ResponseAbstract $response, $data = null): void
    {
        /** @var \Application\Frontend\AppView $view */
        $view = $response->data['Content'];

        $view->data['news'] = \array_slice($this->getNewsList(), 0, 4);
    }

    public function newsView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'news', 'News');
        $view->data['news'] = $this->getNewsList();

        return $view;
    }

    public function newsArticleView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $slug  = $request->uri->getPathElement(1);
        $admin = $this->isAdmin($request);

        /** @var \Models\News $news */
        $news = NewsMapper::get()->where('slug', $slug)->execute();

        // drafts and scheduled posts are only visible for administrators (preview)
        if ($news->id === 0 || (!$news->isVisible() && !$admin)) {
            $response->header->status = RequestStatusCode::R_404;

            return $this->createView($request, $response, 'error/404', 'Not found');
        }

        $list = $this->getNewsList();
        $view = $this->createView($request, $response, 'news_article', $news->title);

        $view->data['article'] = $this->newsToArray($news, 0);
        $view->data['content'] = Markdown::parse($news->content);
        $view->data['admin']   = $admin;
        $view->data['newer']   = null;
        $view->data['older']   = null;

        foreach ($list as $index => $entry) {
            if ($entry['id'] === $news->id) {
                $view->data['newer'] = $list[$index - 1] ?? null;
                $view->data['older'] = $list[$index + 1] ?? null;
            }
        }

        return $view;
    }

    /**
     * Published news posts (newest first)
     *
     * @return array<int, array{id:int, slug:string, date:\DateTime, title:string, excerpt:string, image:string, status:string}>
     */
    private function getNewsList() : array
    {
        /** @var \Models\News[] $posts */
        $posts = NewsMapper::getAll()
            ->where('status', NewsStatus::PUBLISHED)
            ->where('published', (new \DateTime())->format('Y-m-d H:i:s'), '<=')
            ->sort('published', 'DESC')
            ->executeGetArray();

        $list = [];
        foreach (\array_values($posts) as $i => $news) {
            $list[] = $this->newsToArray($news, $i);
        }

        return $list;
    }

    /**
     * News post as array for the templates
     *
     * @param \Models\News $news  News post
     * @param int          $index Position in the list (for the fallback image)
     *
     * @return array
     */
    private function newsToArray(\Models\News $news, int $index) : array
    {
        return [
            'id'      => $news->id,
            'slug'    => $news->slug,
            'date'    => $news->published,
            'title'   => $news->title,
            'excerpt' => $news->summary,
            'image'   => $news->image !== '' ? $news->image : self::NEWS_FALLBACK_IMAGES[$index % \count(self::NEWS_FALLBACK_IMAGES)],
            'status'  => $news->status,
        ];
    }

    /**
     * Is the current user an administrator?
     *
     * @param RequestAbstract $request Request
     *
     * @return bool
     */
    private function isAdmin(RequestAbstract $request) : bool
    {
        return $request->header->account > 0
            && AccountMapper::get()->where('id', $request->header->account)->execute()->isAdmin();
    }

    /* ======================================================================
       Account
       ====================================================================== */

    public function signupView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        return $this->createView($request, $response, 'signup', 'Sign up');
    }

    public function signupSuccessView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        return $this->createView($request, $response, 'signup_success', 'Sign up');
    }

    public function signupConfirmationView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        // @todo Validate the confirmation token once the account backend exists
        //      (previously: $this->app->moduleManager->get('Admin', 'Api')->apiDataChange($request, $response);)
        return $this->createView($request, $response, 'signup_confirmation', 'Account confirmed');
    }

    public function signinView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        $view = $this->createView($request, $response, 'signin', 'Sign in');
        $view->data['redirect'] = $request->getDataString('redirect') ?? '';

        return $view;
    }

    /**
     * Account settings of the signed in user
     */
    public function accountView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        $view = $this->createView($request, $response, 'account', 'Account');

        /** @var \Models\Account $account */
        $account = AccountMapper::get()->where('id', $request->header->account)->execute();

        if ($account->id === 0) {
            $response->header->set('Location', '/signin?redirect=%2Faccount', true);
            $response->header->status = RequestStatusCode::R_303;

            return $view;
        }

        $view->data['account']    = $account;
        $view->data['characters'] = (new ProfileData($this->app->dbPool->get()))->getCharactersOfAccount($account->id, true);
        $view->data['feedback']   = \Models\FeedbackMapper::getAll()->where('account', $account->id)->sort('created', 'DESC')->executeGetArray();

        return $view;
    }

    public function forgotView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        return $this->createView($request, $response, 'forgot', 'Forgot password');
    }

    public function loginView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->signinView($request, $response);
    }

    public function profileView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        if ($request->getRouteVerb() !== RouteVerb::GET) {
            $view = $this->updateProfile($request, $response, $data);
        }

        $view->setTemplate('/Application/Frontend/tpl/view');

        $view->data['backend_allowed'] = $this->app->accountManager->get($request->header->account)
            ->hasPermission(PermissionType::READ, $this->app->unitId, $this->app->appId, 'Dashboard');

        return $view;
    }

    private function updateProfile(RequestAbstract $request, ResponseAbstract $response, $data = null): View
    {
        $internalRequest = new HttpRequest();
        $internalRequest->header->account = $request->header->account;

        $internalRequest->setData('name1', $request->getDataString('name1'));

        $this->app->moduleManager->get('Admin', 'Api')->apiAccountUpdate($internalRequest, $response, $data);

        $request->setData('account', $request->header->account, true);
        $request->setData('id', null, true);
        $request->setData('unit', $this->app->unitId);
        $this->app->moduleManager->get('ClientManagement', 'Api')->apiMainAddressUpdate($request, $response, $data);

        $view = new View($this->app->l11nManager, $request, $response);

        return $view;
    }

    /* ======================================================================
       Game hub & info pages
       ====================================================================== */

    public function gameView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'game', 'Game');
    }

    public function faqView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'faq', 'FAQ');

        /** @var \Models\Faq[] $faqs */
        $faqs = FaqMapper::getAll()->executeGetArray();

        $categories = [];
        foreach ($faqs as $faq) {
            if ($faq->category !== '') {
                $categories[$faq->category] = $faq->category;
            }
        }

        \ksort($categories);

        // Server side filtering (works without JavaScript)
        $category = $request->getDataString('category') ?? '';
        $search   = \trim($request->getDataString('q') ?? '');

        $faqs = \array_filter($faqs, function ($faq) use ($category, $search) : bool {
            if ($category !== '' && $faq->category !== $category) {
                return false;
            }

            return $search === ''
                || \stripos($faq->question, $search) !== false
                || \stripos($faq->answer, $search) !== false;
        });

        \usort($faqs, fn ($a, $b) : int => $a->order <=> $b->order ?: $a->id <=> $b->id);

        $view->data['faq']        = $faqs;
        $view->data['admin']      = $this->isAdmin($request);
        $view->data['categories'] = $categories;
        $view->data['category']   = $category;
        $view->data['search']     = $search;

        return $view;
    }

    public function wikiView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'wiki', 'Wiki');

        $pathElements = $request->uri->getPathElements();
        \array_shift($pathElements);

        // We remove all path elements that are numbers and following path elements
        $foundNumber = false;
        foreach ($pathElements as $key => $element) {
            if ($foundNumber || \is_numeric($element)) {
                unset($pathElements[$key]);

                $foundNumber = true;
            }
        }

        $slug = \strtolower(\implode('/', $pathElements));
        $slug = $slug === '' ? 'index' : $slug;

        /** @var \Models\WikiPage[] $pages */
        $pages = WikiPageMapper::getAll()->executeGetArray();

        $nav = \array_filter($pages, fn ($p) => $p->navOrder !== null);
        \usort($nav, fn ($a, $b) => $a->navOrder <=> $b->navOrder);

        $page = null;
        foreach ($pages as $p) {
            // old links use "_" instead of "/" (e.g. /wiki/item_equipment)
            if ($p->slug === $slug || $p->slug === \str_replace('_', '/', $slug) || \str_replace('/', '_', $p->slug) === $slug) {
                $page = $p;
                break;
            }
        }

        $view->data['slug']  = $slug;
        $view->data['page']  = $page;
        $view->data['nav']   = $nav;
        $view->data['admin'] = $this->isAdmin($request);

        // search and item details are generated pages
        if ($slug === 'search') {
            $query   = \trim($request->getDataString('q') ?? '');
            $results = [];

            if ($query !== '') {
                foreach ($pages as $p) {
                    if (\stripos($p->title, $query) !== false || \stripos(\strip_tags($p->content), $query) !== false) {
                        $results[] = $p;
                    }
                }
            }

            $view->data['query']   = $query;
            $view->data['results'] = $results;
        } elseif ($page === null && $slug !== 'item') {
            $response->header->status = RequestStatusCode::R_404;
        }

        if ($page !== null) {
            $response->data['Content']->head->title .= ' - ' . $page->title;
        }

        return $view;
    }

    public function mapView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'map', 'World Map');
    }

    public function craftView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'craft', 'Crafting');
    }

    public function videosView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'videos', 'Videos');
    }

    public function developmentView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'development', 'Development');
        $view->data['news']       = \array_slice($this->getNewsList(), 0, 3);
        $view->data['milestones'] = MilestoneMapper::getAll()->sort('order', 'ASC')->executeGetArray();
        $view->data['areas']      = DevelopmentAreaMapper::getAll()->sort('order', 'ASC')->executeGetArray();
        $view->data['admin']      = $this->isAdmin($request);

        return $view;
    }

    public function supportView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'support', 'Support us');
    }

    /* ======================================================================
       Competitive & community
       ====================================================================== */

    public function ladderView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'ladder', 'Ladder');

        $category = $request->getDataString('category') ?? '';
        $type     = $request->getDataString('type') ?? '';

        // the category selects the default ladder of a category
        if ($type === '' || $type === 'Type') {
            $type = match ($category) {
                'Player' => 'Achievement',
                'Guild'  => 'Raid',
                default  => 'Level',
            };
        }

        $filter = [
            'type'     => $type,
            'player'   => \trim($request->getDataString('player') ?? ''),
            'server'   => \trim($request->getDataString('server') ?? ''),
            'class'    => \in_array($request->getDataString('class') ?? '', ['', 'Any', 'Class'], true) ? '' : (string) $request->getDataString('class'),
            'mode'     => \in_array($request->getDataString('mode') ?? '', ['', 'Mode'], true) ? '' : (string) $request->getDataString('mode'),
            'seasonal' => ($request->getDataString('time') ?? '') === 'Seasonal',
        ];

        $view->data['filter'] = $filter + ['category' => $category];
        $view->data['ladder'] = (new LadderData($this->app->dbPool->get()))->get($filter);

        return $view;
    }

    public function recordingListView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'recording_list', 'Recordings');
    }

    public function feedbackListView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'feedback_list', 'Feedback');

        $query = \trim($request->getDataString('q') ?? '');
        $type  = $request->getDataString('type') ?? '';
        $state = $request->getDataString('state') ?? '';
        $sort  = $request->getDataString('sort') ?? 'top';

        $view->data['query'] = $query;
        $view->data['type']  = \in_array($type, FeedbackData::TYPES, true) ? $type : '';
        $view->data['state'] = isset(FeedbackData::STATES[$state]) ? $state : '';
        $view->data['sort']  = \in_array($sort, ['top', 'new', 'comments'], true) ? $sort : 'top';

        $view->data['feedbacks'] = (new FeedbackData($this->app->dbPool->get()))->getList(
            $view->data['query'], $view->data['type'], $view->data['state'], $view->data['sort']
        );

        return $view;
    }

    /**
     * Single feedback incl. comments and votes
     *
     * Votes and comments are sent to the API (see ApiController).
     */
    public function feedbackView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $feedback = (new FeedbackData($this->app->dbPool->get()))->get(
            (int) $request->uri->getPathElement(1),
            $request->header->account
        );

        $view = $this->createView($request, $response, 'feedback_view', $feedback['title'] ?? 'Feedback');
        $view->data['feedback'] = $feedback;
        $view->data['account']  = $request->header->account;
        $view->data['viewer']   = AccountMapper::get()->where('id', $request->header->account)->execute();

        if ($feedback === null) {
            $response->header->status = RequestStatusCode::R_404;
        }

        return $view;
    }

    public function feedbackCreateView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'feedback_create', 'Feedback');
        $view->data['account'] = $request->header->account;

        return $view;
    }

    public function buildListView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'build_list', 'Builds');
    }

    public function buildPlannerView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'build_planner', 'Build Planner');
    }

    public function auctionHouseView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'auction_house', 'Auction House');
        $view->data['category'] = $request->uri->getPathElement(1);

        return $view;
    }

    /* ======================================================================
       Profiles
       ====================================================================== */

    public function profilePlayerListView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'profile_player_list', 'Players');

        $search = \trim($request->getDataString('player') ?? '');
        $page   = \max(1, $request->getDataInt('page') ?? 1);
        $result = (new ProfileData($this->app->dbPool->get()))->getPlayers($search, $page);

        $view->data['search'] = $search;
        $view->data['rows']   = $result['rows'];
        $view->data['page']   = $page;
        $view->data['pages']  = \max(1, (int) \ceil($result['count'] / ProfileData::PER_PAGE));

        return $view;
    }

    public function profilePlayerView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view     = $this->createView($request, $response, 'profile_player', 'Player');
        $profiles = new ProfileData($this->app->dbPool->get());
        $account  = $profiles->getAccount((int) $request->uri->getPathElement(2));

        $view->data['profile'] = $account;
        if ($account === null) {
            $response->header->status = RequestStatusCode::R_404;

            return $view;
        }

        $response->data['Content']->head->title .= ' ' . $account->name;

        // Tabs: overview, achievements, seasons, dungeon, raid, worldboss, pvp
        $tab = $request->getDataString('tab') ?? 'overview';
        if (!isset(EncounterData::TYPES[$tab]) && !\in_array($tab, ['achievements', 'seasons'])) {
            $tab = 'overview';
        }

        $view->data['tab']          = $tab;
        $view->data['characters']   = $profiles->getCharactersOfAccount($account->id);
        $view->data['stats']        = $profiles->getAccountStats($account->id);
        $view->data['achievements'] = (new AchievementData($this->app->dbPool->get()))->forAccount($account->id);

        // Seasonal results, one season is shown in detail (default: newest season played)
        if ($tab === 'seasons') {
            $seasons  = (new SeasonData($this->app->dbPool->get()))->forAccount($account->id);
            $selected = $request->getDataInt('season') ?? 0;

            if (!isset($seasons[$selected])) {
                $played   = \array_filter($seasons, fn ($s) => $s['played']);
                $selected = (int) (\array_key_first($played) ?? \array_key_first($seasons) ?? 0);
            }

            $view->data['seasons']        = $seasons;
            $view->data['selectedSeason'] = $selected;
        }

        // Encounter lists
        $view->data['encounters'] = [];
        if (isset(EncounterData::TYPES[$tab])) {
            $encounters = new EncounterData($this->app->dbPool->get());
            $page       = \max(1, $request->getDataInt('page') ?? 1);
            $result     = $encounters->forAccount($account->id, $tab, $page);

            $view->data['encounters'] = $result['encounters'];
            $view->data['page']       = $page;
            $view->data['pages']      = \max(1, (int) \ceil($result['count'] / EncounterData::PER_PAGE));

            if ($tab === 'pvp') {
                $view->data['pvp']    = $encounters->pvpSummary($account->id);
                $view->data['rating'] = $encounters->pvpRating($account->id);
            }
        }

        return $view;
    }

    /**
     * Detail page of a dungeon run, raid, world boss fight or PvP match of a player
     *
     * Url: /player/profile/{player}/{type}/{id}
     */
    public function profileEncounterView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $account   = (new ProfileData($this->app->dbPool->get()))->getAccount((int) $request->uri->getPathElement(2));
        $type      = $request->uri->getPathElement(3);
        $encounter = $account === null
            ? null
            : (new EncounterData($this->app->dbPool->get()))->get($account->id, $type, (int) $request->uri->getPathElement(4));

        if ($encounter === null) {
            $response->header->status = RequestStatusCode::R_404;

            return $this->createView($request, $response, 'error/404', 'Not found');
        }

        $view = $this->createView($request, $response, $type === 'pvp' ? 'profile_pvp_match' : 'profile_encounter', $encounter['title']);
        $view->data['profile']   = $account;
        $view->data['encounter'] = $encounter;

        return $view;
    }

    public function profileCharacterListView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view     = $this->createView($request, $response, 'profile_character_list', 'Characters');
        $profiles = new ProfileData($this->app->dbPool->get());

        $search = \trim($request->getDataString('character') ?? '');
        $server = $request->getDataInt('server') ?? 0;
        $page   = \max(1, $request->getDataInt('page') ?? 1);
        $result = $profiles->getCharacters($search, $server, $page);

        $view->data['search']  = $search;
        $view->data['server']  = $server;
        $view->data['servers'] = $profiles->getServers();
        $view->data['rows']    = $result['rows'];
        $view->data['page']    = $page;
        $view->data['pages']   = \max(1, (int) \ceil($result['count'] / ProfileData::PER_PAGE));

        return $view;
    }

    public function profileCharacterView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view      = $this->createView($request, $response, 'profile_character', 'Character');
        $profiles  = new ProfileData($this->app->dbPool->get());
        $character = $profiles->getCharacter((int) $request->uri->getPathElement(3));

        $view->data['profile'] = $character;
        if ($character === null) {
            $response->header->status = RequestStatusCode::R_404;

            return $view;
        }

        $response->data['Content']->head->title .= ' ' . $character->name;

        $characterData = new CharacterData($this->app->dbPool->get());

        $view->data['character']   = $profiles->characterToArray($character);
        $view->data['race']        = $character->race->name;
        $view->data['rank']        = $profiles->getRank($character);
        $view->data['equipment']   = $characterData->equipment($character->id);
        $view->data['statistics']  = $characterData->statistics($character->id);
        $view->data['professions'] = $characterData->professions($character->id);
        $view->data['progression'] = $characterData->levelProgression($character->id);

        // Tabs: overview, log
        $tab = $request->getDataString('tab') ?? 'overview';
        $view->data['tab'] = \in_array($tab, ['overview', 'log']) ? $tab : 'overview';

        // Event log filter & paging
        $type = $request->getDataString('type') ?? '';
        if (!isset(CharacterData::EVENT_TYPES[$type])) {
            $type = '';
        }

        $perPage = 40;
        $page    = \max(1, $request->getDataInt('page') ?? 1);
        $events  = $view->data['tab'] === 'log'
            ? $characterData->events($character->id, $character->account->id, $type, $page, $perPage)
            : ['events' => [], 'count' => 0];

        $pages = \max(1, (int) \ceil($events['count'] / $perPage));

        $view->data['events']     = $events['events'];
        $view->data['eventCount'] = $events['count'];
        $view->data['eventType']  = $type;
        $view->data['page']       = \min($pages, $page);
        $view->data['pages']      = $pages;

        return $view;
    }

    public function profileGuildListView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view   = $this->createView($request, $response, 'profile_guild_list', 'Guilds');
        $search = \trim($request->getDataString('guild') ?? '');

        $view->data['search'] = $search;
        $view->data['rows']   = (new ProfileData($this->app->dbPool->get()))->getGuilds($search);

        return $view;
    }

    public function profileGuildView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view     = $this->createView($request, $response, 'profile_guild', 'Guild');
        $profiles = new ProfileData($this->app->dbPool->get());
        $guild    = $profiles->getGuild((int) $request->uri->getPathElement(2));

        $view->data['profile'] = $guild;
        if ($guild === null) {
            $response->header->status = RequestStatusCode::R_404;

            return $view;
        }

        $response->data['Content']->head->title .= ' ' . $guild->name;
        $view->data['raidRank'] = $profiles->getRaidRanking()[$guild->id] ?? null;

        return $view;
    }

    public function petProfileView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'pet_profile', 'Pets');
    }

    public function petBattleView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'pet_battle', 'Pet Battles');
    }

    public function petDaycareView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'pet_daycare', 'Pet Day Care');
    }

    public function townView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'town', 'Town Management');
    }

    /* ======================================================================
       Database
       ====================================================================== */

    public function databaseView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'database', 'Database');

        /** @var \Models\Item[] $items */
        $items  = ItemMapper::getAll()->executeGetArray();
        $search = \trim($request->getDataString('q') ?? '');

        $items = \array_filter($items, fn ($item) : bool => \trim($item->name) !== '');

        if ($search !== '') {
            $items = \array_filter($items, fn ($item) : bool => \stripos($item->name, $search) !== false);
        }

        \usort($items, fn ($a, $b) : int => \strcasecmp($a->name, $b->name));

        $perPage = 50;
        $pages   = \max(1, (int) \ceil(\count($items) / $perPage));
        $current = \min($pages, \max(1, $request->getDataInt('page') ?? 1));

        $view->data['items']   = \array_slice($items, ($current - 1) * $perPage, $perPage);
        $view->data['total']   = \count($items);
        $view->data['page']    = $current;
        $view->data['pages']   = $pages;
        $view->data['search']  = $search;

        return $view;
    }

    public function databaseItemView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $id   = (int) $request->uri->getPathElement(2);
        $item = ItemMapper::get()->where('id', $id)->executeGet();

        if ($item->id === 0) {
            return $this->createView($request, $response, 'error/404', 'Not found');
        }

        $view = $this->createView($request, $response, 'database_item', $item->name);
        $view->data['item'] = $item;

        $view->data['recipes'] = RecipeMapper::getAll()
            ->with('ingredients')
            ->with('ingredients/item')
            ->where('for', $id)
            ->executeGetArray();

        return $view;
    }

    public function databaseSkillView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'database_skill', 'Skill');
        $view->data['id'] = (int) $request->uri->getPathElement(2);

        return $view;
    }

    /* ======================================================================
       Shop
       ====================================================================== */

    public function shopView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        // @todo Load the shop items from the item management once the shop backend is available.
        //      The previous implementation queried the itemmgmt_* tables which don't exist in TalesOfSouls.db
        return $this->createView($request, $response, 'shop', 'Shop');
    }

    public function shopItemView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'shop_item', 'Shop');
        $view->data['item'] = $request->uri->getPathElement(2);

        return $view;
    }

    public function apiOneClickBuy(RequestAbstract $request, ResponseAbstract $response, $data = null): void
    {
        if ($request->header->account < 1) {
            $response->header->status = RequestStatusCode::R_303;

            $referer = $request->header->getReferer();
            $response->header->set('Location', empty($referer) ? '/' : $referer, true);

            return;
        }

        $response->set('Content', null, true);
        $response->set($request->uri->__toString(), []);

       $this->app->moduleManager->get('Shop', 'Api')->apiOneClickBuy($request, $response, [
            'success' => UriFactory::build('{/tld}/{/base}/shop/buy/success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel' => UriFactory::build('{/tld}/{/base}/shop')
        ]);
    }

    public function shopBuySuccessView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        // @todo Handle the payment response once the payment backend exists
        //      (previously: $this->app->moduleManager->get('Payment', 'Api')->handlePaymentRequest($request, $response);)
        return $this->createView($request, $response, 'shop_buy_success', 'Thank you');
    }

    /* ======================================================================
       Contact & legal
       ====================================================================== */

    public function contactView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->createView($request, $response, 'contact', 'Contact');
    }

    public function imprintView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->legalView($request, $response, 'imprint', 'Imprint');
    }

    public function privacyView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->legalView($request, $response, 'privacy', 'Privacy Policy');
    }

    public function termsView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->legalView($request, $response, 'terms', 'Terms of Service');
    }

    public function dpaView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->legalView($request, $response, 'dpa', 'Data Processing');
    }

    public function cocView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->legalView($request, $response, 'coc', 'Code of Conduct');
    }

    public function claView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        return $this->legalView($request, $response, 'cla', 'Contributor License Agreement');
    }

    /**
     * Render a markdown document from data/legal
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param string           $name     Document name
     * @param string           $title    Page title
     *
     * @return RenderableInterface
     */
    private function legalView(RequestAbstract $request, ResponseAbstract $response, string $name, string $title) : RenderableInterface
    {
        $view = $this->createView($request, $response, 'markdown', $title);

        $file = self::LEGAL_PATH . '/' . $name . '.md';
        $view->data['markdown'] = \is_file($file)
            ? Markdown::parse((string) \file_get_contents($file))
            : '';

        return $view;
    }

    /* ======================================================================
       Errors
       ====================================================================== */

    public function error403View(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->header->status = RequestStatusCode::R_403;

        return $this->createView($request, $response, 'error/403', 'Access denied');
    }
}
