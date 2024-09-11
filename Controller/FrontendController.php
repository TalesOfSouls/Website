<?php
declare(strict_types=1);

namespace Controller;

use Models\FaqMapper;
use phpOMS\Account\PermissionType;
use phpOMS\Asset\AssetType;
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

    public function frontView(RequestAbstract $request, ResponseAbstract $response, $data = null): void
    {
        $head = $response->data['Content']->head;
        $head->addAsset(AssetType::CSS, 'Application/Frontend/css/front.css?v=1.0.0');

        $view = $response->data['Content'];
    }

    public function signupView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/signup');

        return $view;
    }

    public function signupSuccessView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/signup_success');

        return $view;
    }

    public function signupConfirmationView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        $this->app->moduleManager->get('Admin', 'Api')->apiDataChange($request, $response);

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/signup_confirmation');

        return $view;
    }

    public function signinView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        if ($request->header->account <= 0) {
            $view = new View($this->app->l11nManager, $request, $response);
            $view->setTemplate('/Application/Frontend/tpl/signin');

            return $view;
        }

        $response->data['Content']->setTemplate('/Application/Frontend/tpl/shop-front');

        return $this->shopView($request, $response, $data);
    }

    public function forgotView(RequestAbstract $request, ResponseAbstract $response, array $data = []): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/forgot');

        return $view;
    }

    public function shopView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - Shop';
        $view = $response->data['Content'];

        $items = [];
        $featured = [];

        $query = <<<SQL
        SELECT itemmgmt_item.itemmgmt_item_id, itemmgmt_item.itemmgmt_item_no, media.media_file
        FROM itemmgmt_item
        JOIN itemmgmt_item_attr ON itemmgmt_item.itemmgmt_item_id = itemmgmt_item_attr.itemmgmt_item_attr_item
        JOIN itemmgmt_attr_type ON itemmgmt_item_attr.itemmgmt_item_attr_type = itemmgmt_attr_type.itemmgmt_attr_type_id
        JOIN itemmgmt_attr_value ON itemmgmt_item_attr.itemmgmt_item_attr_value = itemmgmt_attr_value.itemmgmt_attr_value_id
        JOIN itemmgmt_item_media ON itemmgmt_item.itemmgmt_item_id = itemmgmt_item_media.itemmgmt_item_media_item
        JOIN media ON itemmgmt_item_media.itemmgmt_item_media_media = media.media_id
        JOIN media_tag ON media.media_id = media_tag.media_tag_src
        JOIN tag ON media_tag.media_tag_dst = tag.tag_id
        WHERE itemmgmt_item.itemmgmt_item_status = 1
        AND itemmgmt_item.itemmgmt_item_parent IS NULL
        AND (itemmgmt_attr_type.itemmgmt_attr_type_name = 'shop_front' or itemmgmt_attr_type.itemmgmt_attr_type_name = 'shop_featured')
        AND itemmgmt_attr_value.itemmgmt_attr_value_valueInt = 1
        AND tag.tag_name = 'shop_list_image'
        AND EXISTS (
            SELECT 1
            FROM itemmgmt_item_attr AS attr
            JOIN itemmgmt_attr_type AS attr_type ON attr.itemmgmt_item_attr_type = attr_type.itemmgmt_attr_type_id
            JOIN itemmgmt_attr_value AS attr_value ON attr.itemmgmt_item_attr_value = attr_value.itemmgmt_attr_value_id
            WHERE attr.itemmgmt_item_attr_item = itemmgmt_item.itemmgmt_item_id
                AND attr_type.itemmgmt_attr_type_name = 'shop_item'
                AND attr_value.itemmgmt_attr_value_valueInt = 1
        )
        SQL;

        $files = $this->app->dbPool->get()->con->query($query)->fetchAll();

        foreach ($files as $file) {
            if (!isset($items[$file['itemmgmt_item_id']])) {
                $items[$file['itemmgmt_item_id']] = [
                    'number' => '',
                    'file_path' => '',
                    'name1' => '',
                    'description_short' => '',
                ];
            }

            $items[$file['itemmgmt_item_id']]['number'] = $file['itemmgmt_item_no'];
            $items[$file['itemmgmt_item_id']]['file_path'] = $file['media_file'];
        }

        $query = <<<SQL
        SELECT itemmgmt_item.itemmgmt_item_id, itemmgmt_attr_type.itemmgmt_attr_type_name, itemmgmt_item_l11n_type.itemmgmt_item_l11n_type_title, itemmgmt_item_l11n.itemmgmt_item_l11n_description
        FROM itemmgmt_item
        join itemmgmt_item_attr on itemmgmt_item.itemmgmt_item_id = itemmgmt_item_attr.itemmgmt_item_attr_item
        join itemmgmt_attr_type on itemmgmt_item_attr.itemmgmt_item_attr_type = itemmgmt_attr_type.itemmgmt_attr_type_id
        join itemmgmt_attr_value on itemmgmt_item_attr.itemmgmt_item_attr_value = itemmgmt_attr_value.itemmgmt_attr_value_id
        join itemmgmt_item_l11n on itemmgmt_item.itemmgmt_item_id = itemmgmt_item_l11n.itemmgmt_item_l11n_item
        join itemmgmt_item_l11n_type on itemmgmt_item_l11n.itemmgmt_item_l11n_typeref = itemmgmt_item_l11n_type.itemmgmt_item_l11n_type_id
        where itemmgmt_item.itemmgmt_item_status = 1
            and itemmgmt_item.itemmgmt_item_parent is null
            and (itemmgmt_attr_type.itemmgmt_attr_type_name = 'shop_front' or itemmgmt_attr_type.itemmgmt_attr_type_name = 'shop_featured')
            and itemmgmt_attr_value.itemmgmt_attr_value_valueInt = 1
            and itemmgmt_item_l11n.itemmgmt_item_l11n_lang = 'en'
            and itemmgmt_item_l11n_type.itemmgmt_item_l11n_type_title in ('name1', 'description_short')
            AND EXISTS (
                SELECT 1
                FROM itemmgmt_item_attr AS attr
                JOIN itemmgmt_attr_type AS attr_type ON attr.itemmgmt_item_attr_type = attr_type.itemmgmt_attr_type_id
                JOIN itemmgmt_attr_value AS attr_value ON attr.itemmgmt_item_attr_value = attr_value.itemmgmt_attr_value_id
                WHERE attr.itemmgmt_item_attr_item = itemmgmt_item.itemmgmt_item_id
                AND attr_type.itemmgmt_attr_type_name = 'shop_item'
                AND attr_value.itemmgmt_attr_value_valueInt = 1
            )
        SQL;

        $l11n = $this->app->dbPool->get()->con->query($query)->fetchAll();

        foreach ($l11n as $l11nItem) {
            if ($l11nItem['itemmgmt_attr_type_name'] === 'shop_featured') {
                if (!isset($featured[$l11nItem['itemmgmt_item_id']])) {
                    $featured[$l11nItem['itemmgmt_item_id']] = [
                        'number' => '',
                        'file_path' => '',
                        'name1' => '',
                        'description_short' => '',
                    ];
                }

                $featured[$l11nItem['itemmgmt_item_id']][$l11nItem['itemmgmt_item_l11n_type_title']] = $l11nItem['itemmgmt_item_l11n_description'];

                continue;
            }

            if (!isset($items[$l11nItem['itemmgmt_item_id']])) {
                $items[$l11nItem['itemmgmt_item_id']] = [
                    'number' => '',
                    'file_path' => '',
                    'name1' => '',
                    'description_short' => '',
                ];
            }

            $items[$l11nItem['itemmgmt_item_id']][$l11nItem['itemmgmt_item_l11n_type_title']] = $l11nItem['itemmgmt_item_l11n_description'];
        }

        $view->data['items'] = $items;

        $query = <<<SQL
        SELECT itemmgmt_item.itemmgmt_item_id, itemmgmt_item.itemmgmt_item_no, media.media_file
        FROM itemmgmt_item
        join itemmgmt_item_attr on itemmgmt_item.itemmgmt_item_id = itemmgmt_item_attr.itemmgmt_item_attr_item
        join itemmgmt_attr_type on itemmgmt_item_attr.itemmgmt_item_attr_type = itemmgmt_attr_type.itemmgmt_attr_type_id
        join itemmgmt_attr_value on itemmgmt_item_attr.itemmgmt_item_attr_value = itemmgmt_attr_value.itemmgmt_attr_value_id
        join itemmgmt_item_media on itemmgmt_item.itemmgmt_item_id = itemmgmt_item_media.itemmgmt_item_media_item
        join media on itemmgmt_item_media.itemmgmt_item_media_media = media.media_id
        join media_tag on media.media_id = media_tag.media_tag_src
        join tag on media_tag.media_tag_dst = tag.tag_id
        where itemmgmt_item.itemmgmt_item_status = 1
            and itemmgmt_item.itemmgmt_item_parent is null
            and itemmgmt_attr_type.itemmgmt_attr_type_name = 'shop_featured'
            and itemmgmt_attr_value.itemmgmt_attr_value_valueInt = 1
            and tag.tag_name = 'shop_featured_image';
        SQL;

        $files = $this->app->dbPool->get()->con->query($query)->fetchAll();

        foreach ($files as $file) {
            if (!isset($featured[$file['itemmgmt_item_id']])) {
                $featured[$file['itemmgmt_item_id']] = [
                    'number' => '',
                    'file_path' => '',
                    'name1' => '',
                    'description_short' => '',
                ];
            }

            $featured[$file['itemmgmt_item_id']]['number'] = $file['itemmgmt_item_no'];
            $featured[$file['itemmgmt_item_id']]['file_path'] = $file['media_file'];
        }

        $view->data['featured'] = \reset($featured);

        return $view;
    }

    public function newsView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - News';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/news');

        return $view;
    }

    public function gameView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - Game';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/game');

        return $view;
    }

    public function faqView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - FAQ';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/faq');

        $view->data['faq'] = FaqMapper::getAll()->executeGetArray();

        return $view;
    }

    public function wikiView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - Wiki';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/wiki');

        $pathElements = $request->uri->getPathElements(0);
        array_shift($pathElements);
        $path = \implode('_', $pathElements);

        $view->data['path'] = $path;

        return $view;
    }

    public function ladderView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - Ladder';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/ladder');

        return $view;
    }

    public function buildListView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - Builds';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/build_list');

        return $view;
    }

    public function buildPlannerView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - Build';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/build_planner');

        return $view;
    }

    public function auctionHouseView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - Ladder';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/auction_house');

        return $view;
    }

    public function shopItemView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $response->data['Content']->head->title .= ' - Shop';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/shop-item');

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
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/shop-buy-success');

        $bill = $this->app->moduleManager->get('Payment', 'Api')->handlePaymentRequest($request, $response);

        $view->data['bill'] = $bill;

        return $view;
    }

    public function contactView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        if ($request->getRouteVerb() === RouteVerb::GET) {
            $view = new View($this->app->l11nManager, $request, $response);
            $view->setTemplate('/Application/Frontend/tpl/contact');
        } else {
            $view = $this->contactMessage($request, $response, $data);
        }

        return $view;
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

    public function imprintView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/markdown');

        $view->data['markdown'] = Markdown::parse('');

        return $view;
    }

    public function loginView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/login');

        return $view;
    }

    public function privacyView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/markdown');

        $view->data['markdown'] = Markdown::parse('');

        return $view;
    }

    public function termsView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/markdown');

        $view->data['markdown'] = Markdown::parse('');

        return $view;
    }

    public function dpaView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/markdown');

        $view->data['markdown'] = Markdown::parse('');

        return $view;
    }

    public function cocView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/markdown');

        $view->data['markdown'] = Markdown::parse('');

        return $view;
    }

    public function claView(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/markdown');

        $view->data['markdown'] = Markdown::parse('');

        return $view;
    }

    public function contactMessage(RequestAbstract $request, ResponseAbstract $response, $data = null): RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Application/Frontend/tpl/contactConfirmation');

        return $view;
    }
}
