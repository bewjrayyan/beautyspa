<?php

namespace Modules\Storefront\Http\ViewComposers;

use Illuminate\View\View;
use Spatie\SchemaOrg\Schema;
use Illuminate\Support\Str;
use Modules\Meta\Support\OpenGraph;
use Modules\Storefront\Banner;
use Modules\Storefront\Feature;
use Illuminate\Support\Collection;
use Modules\Product\Entities\Product;
use Spatie\SchemaOrg\ItemAvailability;

class ProductShowPageComposer
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $product = $view->getData()['product'];

        $view->with([
            'features' => Feature::all(),
            'banner' => Banner::getProductPageBanner(),
            'productSchemaMarkup' => $this->schemaMarkup($product),
            'categoryBreadcrumb' => $this->getCategoryBreadCrumb($product->categories->nest()),
            'openGraph' => $this->openGraph($product),
        ]);
    }


    private function openGraph(Product $product): OpenGraph
    {
        $variant = $product->variant;
        $imagePath = ($variant && $variant->base_image->id)
            ? $variant->base_image->path
            : ($product->base_image->path ?: null);

        $description = $product->meta->meta_description ?: $product->short_description;

        return OpenGraph::make(
            title: $product->meta->meta_title ?: $product->name,
            description: $description ? (string) $description : $product->name,
            url: $variant?->url() ?? $product->url(),
            type: 'product',
            image: $imagePath,
            imageAlt: $product->name,
            priceAmount: (string) ($variant?->selling_price->convertToCurrentCurrency()->amount()
                ?? $product->selling_price->convertToCurrentCurrency()->amount()),
            priceCurrency: currency(),
        );
    }


    private function schemaMarkup(Product $product)
    {
        $schema = Schema::product()
            ->name($product->name)
            ->sku($product->sku)
            ->url($product->url())
            ->image($product->base_image->path)
            ->brand($this->brandSchema($product))
            ->description($product->short_description)
            ->offers($this->offersSchema($product));

        if ($product->reviews()->count() > 0) {
            $schema->aggregateRating($this->aggregateRatingSchema($product));
        }

        return $schema;
    }


    private function brandSchema(Product $product)
    {
        return Schema::brand()->name($product->brand->name);
    }


    private function aggregateRatingSchema(Product $product)
    {
        return Schema::aggregateRating()
            ->ratingValue($product->reviews()->avg('rating'))
            ->ratingCount($product->reviews()->count());
    }


    private function offersSchema(Product $product)
    {
        return Schema::offer()
            ->price(($product->variant ?? $product)->selling_price->convertToCurrentCurrency()->amount())
            ->priceCurrency(currency())
            ->availability($product->isInStock() ? ItemAvailability::InStock : ItemAvailability::OutOfStock)
            ->url($product->url());
    }


    private function getCategoryBreadCrumb(Collection $categories)
    {
        $breadcrumb = '';

        foreach ($categories as $category) {
            $breadcrumb .= "<li><a href='{$category->url()}'>{$category->name}</a></li>";

            if ($category->items->isNotEmpty()) {
                $breadcrumb .= $this->getCategoryBreadCrumb($category->items);
            }
        }

        return $breadcrumb;
    }
}
