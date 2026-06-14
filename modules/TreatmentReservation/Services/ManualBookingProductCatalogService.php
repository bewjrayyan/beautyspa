<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Collection;
use Modules\Product\Entities\Product;

class ManualBookingProductCatalogService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function catalog(): array
    {
        return Product::query()
            ->where('is_virtual', true)
            ->where('is_active', true)
            ->with([
                'options',
                'options.values' => function ($query) {
                    $query->orderBy('position');
                },
                'variations',
                'variations.values' => function ($query) {
                    $query->orderBy('position');
                },
                'variations.values.files',
                'variants' => function ($query) {
                    $query->where('is_active', true)->orderBy('position');
                },
            ])
            ->orderBy('id')
            ->get()
            ->map(fn (Product $product) => $this->serializeProduct($product))
            ->values()
            ->all();
    }


    /**
     * @return array<string, mixed>
     */
    public function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'formatted_price' => $product->formatted_price,
            'price_amount' => $product->selling_price->amount(),
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
                    'color' => $value->color ?? null,
                    'image' => $value->image?->path,
                ])->values()->all(),
            ])->values()->all(),
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'uid' => $variant->uid,
                'uids' => $variant->uids,
                'name' => $variant->name,
                'formatted_price' => $variant->formatted_price,
                'price_amount' => $variant->selling_price->amount(),
            ])->values()->all(),
            'options' => $product->options->map(fn ($option) => [
                'id' => $option->id,
                'name' => $option->name,
                'type' => $option->type,
                'is_required' => (bool) $option->is_required,
                'values' => $option->values->map(fn ($value) => [
                    'id' => $value->id,
                    'label' => $value->label,
                    'price' => $value->price?->amount() ?? 0,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }


    public function findInCatalog(int $productId, ?Collection $catalog = null): ?array
    {
        $catalog ??= collect($this->catalog());

        return $catalog->firstWhere('id', $productId);
    }
}
