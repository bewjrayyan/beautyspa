<?php

namespace Modules\Storefront\Http\ViewComposers;

use Exception;
use Throwable;
use Illuminate\View\View;
use Spatie\SchemaOrg\Schema;
use Modules\Compare\Compare;
use Modules\Tag\Entities\Tag;
use Modules\Cart\Facades\Cart;
use Modules\Menu\Entities\Menu;
use Modules\Page\Entities\Page;
use Modules\Media\Entities\File;
use Modules\Menu\MegaMenu\MegaMenu;
use Illuminate\Support\Facades\Cache;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\SearchTerm;
use Modules\Product\Services\SearchTermService;

class LayoutComposer
{
    /**
     * @var Compare
     */
    private $compare;


    /**
     * Create a new view composer instance.
     *
     * @param Compare $compare
     */
    public function __construct(Compare $compare)
    {
        $this->compare = $compare;
    }


    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose($view)
    {
        $view->with([
            'themeColor' => $this->getThemeColor(),
            'compareCount' => $this->compare->count(),
            'wishlistCount' => $this->getWishlistCount(),
            'cartQuantity' => $this->getCartQuantity(),
            'favicon' => $this->getFavicon(),
            'logo' => $this->getHeaderLogo(),
            'newsletterBgImage' => $this->getNewsletterBgImage(),
            'privacyPageUrl' => $this->getPrivacyPageUrl(),
            'categories' => $this->getCategories(),
            'mostSearchedKeywords' => $this->getMostSearchedKeywords(),
            'primaryMenu' => $this->getPrimaryMenu(),
            'categoryMenu' => $this->getCategoryMenu(),
            'footerMenuOne' => $this->getFooterMenuOne(),
            'footerMenuTwo' => $this->getFooterMenuTwo(),
            'footerTags' => $this->getFooterTags(),
            'copyrightText' => $this->getCopyrightText(),
            'acceptedPaymentMethodsImage' => $this->getAcceptedPaymentMethodsImage(),
            'schemaMarkup' => $this->getSchemaMarkup(),
        ]);
    }


    private function getWishlistCount()
    {
        if (! auth()->check()) {
            return 0;
        }

        return (int) auth()->user()->wishlist()->count();
    }


    private function getCartQuantity()
    {
        return Cart::instance()->count();
    }


    public function footerTagsCallback($tagIds)
    {
        $tagIds = array_values(array_filter((array) ($tagIds ?? [])));

        return function () use ($tagIds) {
            if ($tagIds === []) {
                return collect();
            }

            return Tag::whereIn('id', $tagIds)
                ->when(!empty($tagIds), function ($query) use ($tagIds) {
                    $tagIdsString = collect($tagIds)->filter()->implode(',');

                    $query->orderByRaw("FIELD(id, {$tagIdsString})");
                })
                ->get();
        };
    }


    private function getThemeColor()
    {
        try {
            return tinycolor(storefront_theme_color());
        } catch (Exception $e) {
            return tinycolor('#0068e1');
        }
    }


    private function getFavicon()
    {
        return $this->getMedia(setting('storefront_favicon'))->path;
    }


    private function getMedia($fileId)
    {
        return Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });
    }


    private function getHeaderLogo()
    {
        return $this->getMedia(setting('storefront_header_logo'))->path;
    }


    private function getNewsletterBgImage()
    {
        return $this->getMedia(setting('storefront_newsletter_bg_image'))->path;
    }


    private function getPrivacyPageUrl()
    {
        try {
            return Cache::tags('settings')->rememberForever('privacy_page_url', function () {
                return Page::urlForPage(setting('storefront_privacy_page'));
            });
        } catch (Throwable) {
            return Page::urlForPage(setting('storefront_privacy_page'));
        }
    }


    private function getCategories()
    {
        return Category::searchable();
    }


    private function getMostSearchedKeywords()
    {
        return Cache::remember(SearchTermService::CACHE_KEY, now()->addHour(), function () {
            return SearchTerm::select('term')->orderByDesc('hits')->take(5)->pluck('term');
        });
    }


    private function getPrimaryMenu()
    {
        return new MegaMenu(setting('storefront_primary_menu'));
    }


    private function getCategoryMenu()
    {
        return new MegaMenu(setting('storefront_category_menu'));
    }


    private function getFooterMenuOne()
    {
        return $this->getFooterMenu(setting('storefront_footer_menu_one'));
    }


    private function getFooterMenu($menuId)
    {
        try {
            return Cache::tags(['menu_items', 'categories', 'pages', 'settings'])
                ->rememberForever(md5("storefront_footer_menu.{$menuId}:" . locale()), function () use ($menuId) {
                    return Menu::for($menuId);
                });
        } catch (Throwable) {
            return Menu::for($menuId);
        }
    }


    private function getFooterMenuTwo()
    {
        return $this->getFooterMenu(setting('storefront_footer_menu_two'));
    }


    private function getFooterTags()
    {
        $tagIds = setting('storefront_footer_tags', []);

        try {
            return Cache::tags(['tags', 'settings'])
                ->rememberForever(
                    md5('storefront_footer_tags:' . serialize($tagIds) . ':' . locale()),
                    $this->footerTagsCallback($tagIds)
                );
        } catch (Throwable) {
            return ($this->footerTagsCallback($tagIds))();
        }
    }


    private function getCopyrightText()
    {
        return strtr(setting('storefront_copyright_text'), [
            '{{ store_url }}' => route('home'),
            '{{ store_name }}' => setting('store_name'),
            '{{ year }}' => date('Y'),
        ]);
    }


    private function getAcceptedPaymentMethodsImage()
    {
        return $this->getMedia(setting('storefront_accepted_payment_methods_image'));
    }


    private function getSchemaMarkup()
    {
        return Schema::webSite()
            ->url(storefront_home_url())
            ->potentialAction($this->searchActionSchema());
    }


    private function searchActionSchema()
    {
        return Schema::searchAction()
            ->target(storefront_route('products.index') . '?query={search_term_string}')
            ->setProperty('query-input', 'required name=search_term_string');
    }
}
