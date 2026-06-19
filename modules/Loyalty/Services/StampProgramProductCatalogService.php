<?php

namespace Modules\Loyalty\Services;

use Illuminate\Support\Collection;
use Modules\Product\Entities\Product;

class StampProgramProductCatalogService
{
    /**
     * @return array<string, mixed>
     */
    public function serializeProduct(Product $product): array
    {
        $product->loadMissing([
            'options.values',
            'variations.values.files',
            'variants',
        ]);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'is_virtual' => (bool) $product->is_virtual,
            'formatted_price' => $product->formatted_price,
            'has_variants' => $product->variants->isNotEmpty(),
            'has_variations' => $product->variations->isNotEmpty(),
            'has_options' => $product->options->isNotEmpty(),
            'variations' => $product->variations->map(fn ($variation) => [
                'id' => $variation->id,
                'uid' => $variation->uid,
                'name' => $variation->name,
                'type' => $variation->type,
                'values' => $variation->values->map(fn ($value) => [
                    'id' => $value->id,
                    'uid' => $value->uid,
                    'label' => $value->label,
                ])->values()->all(),
            ])->values()->all(),
            'options' => $product->options->map(fn ($option) => [
                'id' => $option->id,
                'name' => $option->name,
                'type' => $option->type,
                'is_required' => (bool) $option->is_required,
                'values' => $option->values->map(fn ($value) => [
                    'id' => $value->id,
                    'label' => $value->label,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }


    /**
     * @return Collection<int, Product>
     */
    public function search(string $term, int $limit = 15, ?int $categoryId = null): Collection
    {
        $model = new Product();

        $baseQuery = $model->newQuery()
            ->withoutGlobalScope('active')
            ->select('products.*')
            ->where('is_active', true);

        if ($categoryId) {
            $baseQuery->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            });
        }

        if ($term === '') {
            return (clone $baseQuery)
                ->orderByDesc('products.id')
                ->limit($limit)
                ->get();
        }

        $scoutResults = $model->search($term)
            ->query()
            ->withoutGlobalScope('active')
            ->mergeConstraintsFrom($baseQuery)
            ->limit($limit)
            ->get();

        if ($scoutResults->isNotEmpty()) {
            return $scoutResults;
        }

        return (clone $baseQuery)
            ->whereHas('translations', function ($translationQuery) use ($term) {
                $translationQuery->where('name', 'like', '%'.$term.'%');
            })
            ->orderByDesc('products.id')
            ->limit($limit)
            ->get();
    }


    /**
     * @return Collection<int, Product>
     */
    public function forCategory(int $categoryId, int $limit = 500): Collection
    {
        return $this->search('', $limit, $categoryId);
    }


    public function formatSearchOption(Product $product): array
    {
        $suffix = $product->is_virtual
            ? ' · '.trans('product::products.table.virtual_treatment')
            : '';

        return [
            'id' => $product->id,
            'name' => trim($product->name).$suffix,
        ];
    }
}
