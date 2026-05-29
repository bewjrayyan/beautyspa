<?php

namespace Modules\Product\Listeners;

use Modules\Product\Entities\Product;

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
        return $product
            ->variants()
            ->withoutGlobalScope('active')
            ->pluck('id')
            ->diff(array_pluck($this->variants(), 'id'));
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

            $product->variants()->withoutGlobalScope('active')->updateOrCreate(
                ['id' => $attributes['id'] ?? null],
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

        $additionalImages = $product->filterFiles('additional_images')->pluck('id')->all();

        if ($additionalImages !== []) {
            $files['additional_images'] = $additionalImages;
        }

        $defaultVariant->syncFiles($files);
    }
}
