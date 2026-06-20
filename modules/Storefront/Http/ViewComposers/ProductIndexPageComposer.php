<?php

namespace Modules\Storefront\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Modules\Support\Money;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\ProductVariant;

class ProductIndexPageComposer
{
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
            'categories' => $this->categories(),
            'minPrice' => $this->minPrice(),
            'maxPrice' => $this->maxPrice(),
            'latestProducts' => $this->latestProducts(),
        ]);
    }


    private function categories()
    {
        return Category::tree();
    }


    private function minPrice()
    {
        return $this->priceRange()['min'];
    }


    private function maxPrice()
    {
        return $this->priceRange()['max'];
    }


    private function priceRange(): array
    {
        return Cache::remember(
            md5('storefront_product_price_range:' . currency()),
            now()->addMinutes(30),
            function () {
                $minProductPrice = Product::min('selling_price');
                $minVariantPrice = ProductVariant::min('selling_price');
                $maxProductPrice = Product::max('selling_price');
                $maxVariantPrice = ProductVariant::max('selling_price');

                $minPrice = min($minProductPrice, $minVariantPrice);
                $maxPrice = max($maxProductPrice, $maxVariantPrice);

                return [
                    'min' => Money::inDefaultCurrency($minPrice)
                        ->convertToCurrentCurrency()
                        ->floor()
                        ->amount(),
                    'max' => Money::inDefaultCurrency($maxPrice)
                        ->convertToCurrentCurrency()
                        ->ceil()
                        ->amount(),
                ];
            }
        );
    }


    private function latestProducts()
    {
        return Product::forCard()->take(5)->latest()->get()->map->clean();
    }
}
