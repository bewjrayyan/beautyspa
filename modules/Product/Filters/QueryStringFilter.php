<?php

namespace Modules\Product\Filters;

use Modules\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeValue;

class QueryStringFilter
{
    private $sorts = [
        'relevance',
        'alphabetic',
        'toprated',
        'latest',
        'pricelowtohigh',
        'pricehightolow',
    ];

    private $groupColumns = [
        'products.id',
        'slug',
        'price',
        'selling_price',
        'special_price',
        'special_price_type',
        'special_price_start',
        'special_price_end',
        'in_stock',
        'manage_stock',
        'qty',
        'new_from',
        'new_to',
    ];


    public function sort($query, $sortType)
    {
        if ($this->sortTypeExists($sortType)) {
            return $this->{$sortType}($query);
        }
    }


    public function relevance()
    {
        // Products are searched by relevant order by default.
    }


    public function alphabetic($query)
    {
        $query->join('product_translations', function (JoinClause $join) {
            $join->on('products.id', '=', 'product_translations.product_id');
        })
            ->groupBy(array_merge($this->groupColumns, ['product_translations.name']))
            ->orderBy('product_translations.name');
    }


    public function topRated($query)
    {
        $query->selectRaw('AVG(reviews.rating) as avg_rating')
            ->leftJoin('reviews', function (JoinClause $join) {
                $join->on('products.id', '=', 'reviews.product_id');
                $join->on('reviews.is_approved', '=', DB::raw('1'));
            })
            ->groupBy($this->groupColumns)
            ->orderByDesc('avg_rating');
    }


    public function latest($query)
    {
        $query->latest();
    }


    public function priceLowToHigh($query)
    {
        $query->orderBy('selling_price');
    }


    public function priceHighToLow($query)
    {
        $query->orderByDesc('selling_price');
    }


    public function fromPrice($query, $price)
    {
        $convertedPrice = $this->convertPrice($price);

        $query->where(function ($productQuery) use ($convertedPrice) {
            $productQuery->where('selling_price', '>=', $convertedPrice);
            $productQuery->orWhereHas('variants', function ($variantQuery) use ($convertedPrice) {
                $variantQuery->where('selling_price', '>=', $convertedPrice);
            });
        });
    }


    public function toPrice($query, $price)
    {
        $convertedPrice = $this->convertPrice($price);

        $query->where(function ($productQuery) use ($convertedPrice) {
            $productQuery->where('selling_price', '<=', $convertedPrice);
            $productQuery->orWhereHas('variants', function ($variantQuery) use ($convertedPrice) {
                $variantQuery->where('selling_price', '<=', $convertedPrice);
            });
        });
    }


    public function brand($query, $slug)
    {
        $query->whereHas('brand', function ($brandQuery) use ($slug) {
            $brandQuery->where('slug', $slug);
        });
    }


    public function category($query, $slug)
    {
        $query->whereHas('categories', function ($categoryQuery) use ($slug) {
            $categoryQuery->where('slug', $slug);
        });
    }


    public function tag($query, $slug)
    {
        $query->whereHas('tags', function ($tagQuery) use ($slug) {
            $tagQuery->where('slug', $slug);
        });
    }


    public function attribute($query, $attributeFilters)
    {
        $valueIds = $this->getAttributeValueIds($attributeFilters);

        if ($valueIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        foreach ($this->getAttributeIds($attributeFilters) as $index => $attributeId) {
            $alias = "pa_{$index}";

            $query->join("product_attributes as {$alias}", 'products.id', '=', "{$alias}.product_id")
                ->where("{$alias}.attribute_id", (int) $attributeId)
                ->whereExists(function ($subQuery) use ($alias, $valueIds) {
                    $subQuery->selectRaw('1')
                        ->from('product_attribute_values')
                        ->whereColumn("{$alias}.id", 'product_attribute_values.product_attribute_id')
                        ->whereIn('attribute_value_id', $valueIds);
                });
        }
    }


    private function sortTypeExists($sortType)
    {
        return in_array(strtolower($sortType), $this->sorts);
    }


    private function convertPrice($price)
    {
        return Money::inCurrentCurrency($price)->convertToDefaultCurrency()->amount();
    }


    private function getAttributeIds($attributeFilters)
    {
        return Attribute::whereIn('slug', array_keys($attributeFilters))->pluck('id');
    }


    private function getAttributeValueIds($attributeFilters)
    {
        return once(function () use ($attributeFilters) {
            return AttributeValue::whereTranslationIn('value', array_flatten($attributeFilters))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        });
    }
}
