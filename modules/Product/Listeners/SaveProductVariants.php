<?php

namespace Modules\Product\Listeners;

use Illuminate\Support\Arr;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

class SaveProductVariants
{
    /**
     * Handle the event.
     *
     * @param Product $product
     *
     * @return void
     */
    public function handle($product)
    {
        $ids = $this->getDeleteCandidates($product);

        if ($ids->isNotEmpty()) {
            $product->variants()->forceDelete($ids);
        }

        $this->saveVariants($product);
    }


    private function getDeleteCandidates($product)
    {
        $requestedIds = collect($this->variants())
            ->pluck('id')
            ->filter(fn ($id) => $id !== null && $id !== '');

        // Never mass-delete when the payload has no valid variant IDs.
        if ($requestedIds->isEmpty()) {
            return collect();
        }

        return $product
            ->variants()
            ->withoutGlobalScope('active')
            ->pluck('id')
            ->diff($requestedIds);
    }


    private function variants()
    {
        return request('variants', []);
    }


    private function saveVariants($product)
    {
        $variants = array_values($this->variants());

        if ($variants === []) {
            return;
        }

        $defaultAssigned = false;
        $counter = 0;

        foreach ($variants as $attributes) {
            if (! empty($attributes['is_default'])) {
                if ($defaultAssigned) {
                    $attributes['is_default'] = false;
                } else {
                    $defaultAssigned = true;
                }
            }

            $attributes['position'] = ++$counter;

            $variantId = $attributes['id'] ?? null;
            $attributes = Arr::only($attributes, (new ProductVariant())->getFillable());

            $product->variants()->withoutGlobalScope('active')->updateOrCreate(
                ['id' => $variantId],
                $attributes
            );
        }

        $this->syncDefaultVariantMediaFromProduct($product);
    }


    private function syncDefaultVariantMediaFromProduct($product): void
    {
        $defaultVariant = $product->variants()
            ->withoutGlobalScope('active')
            ->where('is_default', true)
            ->first()
            ?? $product->variants()->withoutGlobalScope('active')->orderBy('position')->first();

        if (! $defaultVariant) {
            return;
        }

        if ($defaultVariant->filterFiles('base_image')->exists()) {
            return;
        }

        $baseImage = $product->filterFiles('base_image')->first();

        if (! $baseImage) {
            return;
        }

        $files = [
            'base_image' => [$baseImage->id],
        ];

        $additionalImages = $product->filterFiles('additional_images')->pluck('files.id')->all();

        if ($additionalImages !== []) {
            $files['additional_images'] = $additionalImages;
        }

        $defaultVariant->syncFiles($files);
    }
}
