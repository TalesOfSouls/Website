<?php
declare(strict_types=1);

namespace Controller;

use Models\AccountMapper;
use Models\CharacterMapper;
use Models\GuildMapper;
use Models\FaqMapper;
use Models\ItemMapper;
use Models\RecipeMapper;
use Application\Frontend\SampleAchievements;
use Application\Frontend\SampleCharacterHistory;
use Application\Frontend\SampleEncounters;
use Application\Frontend\SampleFeedback;
use Application\Frontend\SampleSeasons;
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
     * Directory containing the news markdown files (data/news/YYYY/YYYY_MM_DD_slug.md)
     *
     * @var string
     * @since 1.0.0
     */
    private const NEWS_PATH = __DIR__ . '/../Application/Frontend/data/news';

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
        $slug = $request->uri->getPathElement(1);
        $list = $this->getNewsList();

        foreach ($list as $index => $news) {
            if ($news['slug'] !== $slug) {
                continue;
            }

            $view = $this->createView($request, $response, 'news_article', $news['title']);
            $view->data['article'] = $news;
            $view->data['content'] = Markdown::parse($this->getNewsContent($news['file']));
            $view->data['newer']   = $list[$index - 1] ?? null;
            $view->data['older']   = $list[$index + 1] ?? null;

            return $view;
        }

        return $this->createView($request, $response, 'error/404', 'Not found');
    }

    /**
     * Get all news posts sorted by date (newest first)
     *
     * @return array<int, array{slug:string, file:string, date:\DateTime, title:string, excerpt:string, image:string}>
     */
    private function getNewsList() : array
    {
        $files = \glob(self::NEWS_PATH . '/*/*.md');
        if ($files === false) {
            return [];
        }

        $list = [];
        foreach ($files as $file) {
            $name = \basename($file, '.md');
            if (\preg_match('/^(\d{4})_(\d{2})_(\d{2})_(.+)$/', $name, $match) !== 1) {
                continue;
            }

            $raw   = \trim((string) \file_get_contents($file));
            $title = \ucfirst(\str_replace('_', ' ', $match[4]));

            if (\preg_match('/^#\s+(.+)$/m', $raw, $heading) === 1) {
                $title = \trim($heading[1]);
            }

            // first real paragraph = excerpt
            $excerpt = '';
            foreach (\preg_split('/\R\R+/', $raw) ?: [] as $block) {
                $block = \trim($block);
                if ($block === '' || $block[0] === '#' || $block[0] === '!' || $block[0] === '*' || $block[0] === '-') {
                    continue;
                }

                $excerpt = \strip_tags($block);
                break;
            }

            if (\mb_strlen($excerpt) > 220) {
                $excerpt = \mb_substr($excerpt, 0, 217) . '...';
            }

            $image = '';
            if (\preg_match('/!\[[^\]]*\]\(([^)]+)\)/', $raw, $img) === 1) {
                $image = $this->rewriteNewsPaths($img[1]);
            }

            $list[] = [
                'slug'    => \strtolower(\str_replace('_', '-', $name)),
                'file'    => $file,
                'date'    => new \DateTime($match[1] . '-' . $match[2] . '-' . $match[3]),
                'title'   => $title,
                'excerpt' => $excerpt,
                'image'   => $image,
            ];
        }

        \usort($list, fn (array $a, array $b) : int => $b['date'] <=> $a['date']);

        foreach ($list as $i => $news) {
            if ($news['image'] === '') {
                $list[$i]['image'] = self::NEWS_FALLBACK_IMAGES[$i % \count(self::NEWS_FALLBACK_IMAGES)];
            }
        }

        return $list;
    }

    /**
     * Load the markdown of a news post without its title (the title is rendered by the template)
     *
     * @param string $file News file
     *
     * @return string
     */
    private function getNewsContent(string $file) : string
    {
        $raw = (string) \file_get_contents($file);
        $raw = \preg_replace('/^#\s+.+$/m', '', $raw, 1) ?? $raw;

        return $this->rewriteNewsPaths($raw);
    }

    /**
     * News markdown files reference images relative to the markdown file (../img/...).
     * The website uses <base href="/"> which is why these paths need to be absolute.
     *
     * @param string $text Text
     *
     * @return string
     */
    private function rewriteNewsPaths(string $text) : string
    {
        return \str_replace('](../img/', '](/Application/Frontend/data/news/img/', \str_replace('../img/', '/Application/Frontend/data/news/img/', $text));
    }

    /* ======================================================================
       Account
       ====================================================================== */

    public function signupView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        // @todo Create the account once the account backend exists
        if ($request->getRouteVerb() === RouteVerb::SET) {
            return $this->createView($request, $response, 'signup_success', 'Sign up');
        }

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

        // @todo Authenticate once the account backend exists
        $view->data['error'] = $request->getRouteVerb() === RouteVerb::SET
            ? 'Signing in is not available yet. Accounts will open with the first beta.'
            : '';

        return $view;
    }

    public function forgotView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        $view = $this->createView($request, $response, 'forgot', 'Forgot password');

        // @todo Send the reset mail once the account backend exists
        $view->data['sent'] = $request->getRouteVerb() === RouteVerb::SET;

        return $view;
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

        $path = \strtolower(\implode('_', $pathElements));

        // Only allow simple template names
        if (\preg_match('/^[a-z_]*$/', $path) !== 1) {
            $path = '404';
        }

        $view->data['path'] = $path === '' ? 'index' : $path;

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
        $view->data['news'] = \array_slice($this->getNewsList(), 0, 3);

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
        return $this->createView($request, $response, 'ladder', 'Ladder');
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
        $view->data['type']  = \in_array($type, SampleFeedback::TYPES, true) ? $type : '';
        $view->data['state'] = isset(SampleFeedback::STATES[$state]) ? $state : '';
        $view->data['sort']  = \in_array($sort, ['top', 'new', 'comments'], true) ? $sort : 'top';

        $view->data['feedbacks'] = SampleFeedback::filter(
            SampleFeedback::all($this->getSampleProfiles()),
            $view->data['query'], $view->data['type'], $view->data['state'], $view->data['sort']
        );

        return $view;
    }

    /**
     * Single feedback incl. comments and votes
     *
     * Votes and comments are sent as normal form posts (no JavaScript).
     *
     * @todo Require a signed in account, store votes (one per account) and comments
     */
    public function feedbackView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $id       = (int) $request->uri->getPathElement(1);
        $feedback = SampleFeedback::get($id, $this->getSampleProfiles());

        $view = $this->createView($request, $response, 'feedback_view', $feedback['title'] ?? 'Feedback');
        $view->data['feedback'] = $feedback;

        if ($feedback === null) {
            $response->header->status = RequestStatusCode::R_404;

            return $view;
        }

        $view->data['vote']         = '';
        $view->data['commentVotes'] = [];
        $view->data['newComment']   = null;
        $view->data['commentError'] = false;

        if ($request->getRouteVerb() === RouteVerb::SET) {
            $vote = $request->getDataString('vote') ?? '';
            $vote = \in_array($vote, ['up', 'down'], true) ? $vote : '';

            switch ($request->getDataString('action') ?? '') {
                case 'vote':
                    $view->data['vote'] = $vote;
                    break;
                case 'comment-vote':
                    $commentId = (int) ($request->getDataString('comment') ?? 0);
                    if ($vote !== '' && $commentId > 0) {
                        $view->data['commentVotes'][$commentId] = $vote;
                    }
                    break;
                case 'comment':
                    $text = \trim($request->getDataString('text') ?? '');
                    if ($text === '' || \mb_strlen($text) > 2000) {
                        $view->data['commentError'] = true;
                    } else {
                        $view->data['newComment'] = ['text' => $text, 'date' => new \DateTime('now')];
                    }
                    break;
            }
        }

        return $view;
    }

    public function feedbackCreateView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'feedback_create', 'Feedback');

        // @todo Store the feedback once the feedback table exists
        $view->data['sent'] = $request->getRouteVerb() === RouteVerb::SET;

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
        $view->data['profiles'] = AccountMapper::getAll()->executeGetArray();
        $view->data['sample']   = $this->getSampleProfiles();

        return $view;
    }

    public function profilePlayerView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'profile_player', 'Player');

        $view->data['sample']  = $this->getSampleProfiles();
        $view->data['profile'] = $this->findProfile(AccountMapper::class, (int) $request->uri->getPathElement(2), $view->data['sample']['players']);

        // Tabs: overview, achievements, seasons, dungeon, raid, worldboss, pvp
        $tab = $request->getDataString('tab') ?? 'overview';
        if (!isset(SampleEncounters::TYPES[$tab]) && !\in_array($tab, ['achievements', 'seasons'])) {
            $tab = 'overview';
        }

        $since = $view->data['sample']['players'][$view->data['profile']->id]['since'] ?? '2024-07-01';
        $view->data['achievements'] = $view->data['profile']->id > 0
            ? SampleAchievements::forPlayer($view->data['profile']->id, $since)
            : [];

        // Seasonal results, one season is shown in detail (default: newest season played)
        if ($tab === 'seasons') {
            $seasons = $view->data['profile']->id > 0
                ? SampleSeasons::forPlayer($view->data['profile']->id, $since)
                : [];

            $selected = (int) ($request->getDataString('season') ?? 0);
            if (!isset($seasons[$selected])) {
                $played   = \array_filter($seasons, fn ($s) => $s['played']);
                $selected = (int) (\array_key_first($played) ?? \array_key_first($seasons) ?? 0);
            }

            $view->data['seasons']        = $seasons;
            $view->data['selectedSeason'] = $selected;
        }

        $view->data['tab']        = $tab;
        $view->data['encounters'] = !isset(SampleEncounters::TYPES[$tab])
            ? []
            : SampleEncounters::forPlayer($view->data['profile']->id, $tab, $view->data['sample']);

        return $view;
    }

    /**
     * Detail page of a dungeon run, raid or world boss fight of a player
     *
     * Url: /player/profile/{player}/{type}/{id}
     */
    public function profileEncounterView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $samples  = $this->getSampleProfiles();
        $profile  = $this->findProfile(AccountMapper::class, (int) $request->uri->getPathElement(2), $samples['players']);
        $type     = $request->uri->getPathElement(3);

        $encounter = $profile->id === 0
            ? null
            : SampleEncounters::get($profile->id, $type, (int) $request->uri->getPathElement(4), $samples);

        if ($encounter === null) {
            return $this->createView($request, $response, 'error/404', 'Not found');
        }

        $view = $this->createView($request, $response, $type === 'pvp' ? 'profile_pvp_match' : 'profile_encounter', $encounter['title']);
        $view->data['profile']   = $profile;
        $view->data['encounter'] = $encounter;
        $view->data['sample']    = $samples;

        return $view;
    }

    public function profileCharacterListView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'profile_character_list', 'Characters');
        $view->data['profiles'] = CharacterMapper::getAll()->executeGetArray();
        $view->data['sample']   = $this->getSampleProfiles();

        return $view;
    }

    public function profileCharacterView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'profile_character', 'Character');

        $view->data['sample']  = $this->getSampleProfiles();
        $view->data['profile'] = $this->findProfile(CharacterMapper::class, (int) $request->uri->getPathElement(3), $view->data['sample']['characters']);

        // Tabs: overview, log
        $tab = $request->getDataString('tab') ?? 'overview';
        $view->data['tab'] = \in_array($tab, ['overview', 'log']) ? $tab : 'overview';

        // Event log filter & paging
        $character = $view->data['sample']['characters'][$view->data['profile']->id] ?? null;
        $events    = $character !== null && $view->data['tab'] === 'log'
            ? SampleCharacterHistory::events($view->data['profile']->id, $character, $view->data['sample'])
            : [];

        $type = $request->getDataString('type') ?? '';
        if (isset(SampleCharacterHistory::EVENT_TYPES[$type])) {
            $events = \array_values(\array_filter($events, fn ($event) => $event['type'] === $type));
        } else {
            $type = '';
        }

        $perPage = 40;
        $pages   = \max(1, (int) \ceil(\count($events) / $perPage));
        $page    = \min($pages, \max(1, $request->getDataInt('page') ?? 1));

        $view->data['events']     = \array_slice($events, ($page - 1) * $perPage, $perPage);
        $view->data['eventCount'] = \count($events);
        $view->data['eventType']  = $type;
        $view->data['page']       = $page;
        $view->data['pages']      = $pages;

        return $view;
    }

    public function profileGuildListView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'profile_guild_list', 'Guilds');
        $view->data['profiles'] = GuildMapper::getAll()->executeGetArray();
        $view->data['sample']   = $this->getSampleProfiles();

        return $view;
    }

    public function profileGuildView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = $this->createView($request, $response, 'profile_guild', 'Guild');

        $view->data['sample']  = $this->getSampleProfiles();
        $view->data['profile'] = $this->findProfile(GuildMapper::class, (int) $request->uri->getPathElement(2), $view->data['sample']['guilds']);

        return $view;
    }

    /**
     * Find a profile model by id.
     *
     * The profile models have no Null* counterpart, which is why ::get() would fail for unknown ids.
     * Falls back to the sample profiles (see data/sample_profiles.php).
     * Returns a model with id 0 if nothing was found (the templates render a 404 in that case).
     *
     * @param string $mapper  Mapper class
     * @param int    $id      Model id
     * @param array  $samples Sample profiles of this type (id => data)
     *
     * @return object
     */
    private function findProfile(string $mapper, int $id, array $samples = []) : object
    {
        $result = $id > 0
            ? $mapper::getAll()->where('id', $id)->executeGetArray()
            : [];

        if (empty($result)) {
            $model = $mapper::MODEL;
            $model = new $model();

            // @todo Remove the sample fallback once real data exists
            if (isset($samples[$id])) {
                $model->id   = $id;
                $model->name = $samples[$id]['name'];
            }

            return $model;
        }

        return \reset($result);
    }

    /**
     * Sample players, characters and guilds
     *
     * @return array{players:array, characters:array, guilds:array}
     */
    private function getSampleProfiles() : array
    {
        return include __DIR__ . '/../Application/Frontend/data/sample_profiles.php';
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
        if ($request->getRouteVerb() === RouteVerb::GET) {
            return $this->createView($request, $response, 'contact', 'Contact');
        }

        return $this->contactMessage($request, $response, $data);
    }

    public function contactMessage(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        // @todo Send/store the contact message
        return $this->createView($request, $response, 'contact_confirmation', 'Contact');
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
