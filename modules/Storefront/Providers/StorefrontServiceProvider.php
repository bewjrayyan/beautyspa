<?php

namespace Modules\Storefront\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Brand\Entities\Brand;
use Modules\Category\Entities\Category;
use Modules\FlashSale\Entities\FlashSale;
use Modules\FlashSale\Entities\FlashSaleProduct;
use Modules\Page\Listeners\ClearPageResponseCache;
use Modules\Product\Entities\Product;
use Modules\Slider\Entities\Slider;
use Modules\Storefront\Admin\StorefrontTabs;
use Modules\Storefront\Http\ViewComposers\LayoutComposer;
use Modules\Storefront\Http\ViewComposers\HomePageComposer;
use Modules\Storefront\Http\ViewComposers\AuthLayoutComposer;
use Modules\Storefront\Http\ViewComposers\StorefrontTabsComposer;
use Modules\Storefront\Http\ViewComposers\BlogPostShowComposer;
use Modules\Storefront\Http\ViewComposers\ProductShowPageComposer;
use Modules\Storefront\Http\ViewComposers\ProductIndexPageComposer;

class StorefrontServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!config('app.installed')) {
            return;
        }

        if (!is_null(setting('storefront_active_flash_sale_campaign'))) {
            FlashSale::activeCampaign(setting('storefront_active_flash_sale_campaign'));
        }

        TabManager::register('storefront', StorefrontTabs::class);

        View::composer('storefront::public.layout', LayoutComposer::class);
        View::composer('storefront::public.auth.*', AuthLayoutComposer::class);
        View::composer('storefront::public.home.index', HomePageComposer::class);
        View::composer('storefront::public.products.index', ProductIndexPageComposer::class);
        View::composer('storefront::public.products.show', ProductShowPageComposer::class);
        View::composer('storefront::public.blogs.posts.show', BlogPostShowComposer::class);
        View::composer('storefront::admin.storefront.tabs.*', StorefrontTabsComposer::class);

        Paginator::defaultView('storefront::public.pagination');

        $this->invalidateResponseCacheOnContentChange();
    }


    /**
     * Drop the cached storefront HTML whenever something the home page renders
     * changes.
     *
     * Page, BlogPost and Setting already flush themselves. The models below are
     * the rest of what HomePageComposer and the home sections pull in; without
     * this an edited slider or price stays invisible until the TTL expires.
     */
    private function invalidateResponseCacheOnContentChange(): void
    {
        $models = [
            Slider::class,
            Category::class,
            Brand::class,
            Product::class,
            FlashSale::class,
            FlashSaleProduct::class,
        ];

        foreach ($models as $model) {
            $model::saved(fn () => ClearPageResponseCache::flush());
            $model::deleted(fn () => ClearPageResponseCache::flush());
        }
    }
}
