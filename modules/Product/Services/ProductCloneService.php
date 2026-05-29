<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Media\Entities\File;
use Modules\Option\Entities\Option;
use Modules\Option\Entities\OptionValue;
use Modules\Attribute\Entities\ProductAttributeValue;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Variation\Entities\Variation;
use Modules\Variation\Entities\VariationValue;
use Illuminate\Support\Facades\Storage;

class ProductCloneService
{
    /** @var array<string, string> */
    private array $variationValueUidMap = [];

    public function clone(Product $source): Product
    {
        return DB::transaction(function () use ($source) {
            $source->load([
                'translations',
                'categories',
                'tags',
                'meta.translations',
                'attributes.values',
                'variations' => function ($query) {
                    $query->with([
                        'translations',
                        'values' => function ($values) {
                            $values->with(['translations', 'files']);
                        },
                    ]);
                },
                'variants' => function ($query) {
                    $query->withoutGlobalScope('active')->with('files');
                },
                'options' => function ($query) {
                    $query->with(['translations', 'values.translations']);
                },
                'files',
            ]);

            $clone = $this->cloneProduct($source);

            $this->cloneEntityFiles($source, $clone, ['base_image', 'additional_images', 'downloads']);
            $this->cloneCategoriesAndTags($source, $clone);
            $this->cloneMeta($source, $clone);
            $this->cloneAttributes($source, $clone);
            $this->cloneVariations($source, $clone);
            $this->cloneVariants($source, $clone);
            $this->cloneOptions($source, $clone);
            $this->cloneProductRelations($source, $clone);

            return $clone->fresh([
                'translations',
                'categories',
                'tags',
                'variations.values',
                'variants',
                'options.values',
            ]);
        });
    }

    private function cloneProduct(Product $source): Product
    {
        return Product::withoutEvents(function () use ($source) {
            $attributes = collect($source->getAttributes())
                ->only($source->getFillable())
                ->except(['id'])
                ->all();

            $attributes['viewed'] = 0;

            if (! empty($attributes['sku'])) {
                $attributes['sku'] = $attributes['sku'] . '-copy';
            }

            $clone = new Product($attributes);

            foreach ($source->translations as $translation) {
                $locale = $translation->locale;

                $clone->translateOrNew($locale)->name = $translation->name . ' (Copy)';
                $clone->translateOrNew($locale)->description = $translation->description;
                $clone->translateOrNew($locale)->short_description = $translation->short_description;
            }

            $clone->save();

            return $clone;
        });
    }

    private function cloneCategoriesAndTags(Product $source, Product $clone): void
    {
        $clone->categories()->sync($source->categories->pluck('id'));
        $clone->tags()->sync($source->tags->pluck('id'));
    }

    private function cloneMeta(Product $source, Product $clone): void
    {
        if (! $source->meta || ! $source->meta->exists) {
            return;
        }

        foreach ($source->meta->translations as $translation) {
            $clone->meta->translateOrNew($translation->locale)->fill([
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
            ]);
        }

        $clone->meta->save();
    }

    private function cloneAttributes(Product $source, Product $clone): void
    {
        foreach ($source->attributes as $attribute) {
            $productAttribute = $clone->attributes()->create([
                'attribute_id' => $attribute->attribute_id,
            ]);

            $values = $attribute->values->map(function ($value) use ($productAttribute) {
                return [
                    'product_attribute_id' => $productAttribute->id,
                    'attribute_value_id' => $value->getRawOriginal('attribute_value_id'),
                ];
            })->all();

            if ($values !== []) {
                ProductAttributeValue::insert($values);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function cloneVariations(Product $source, Product $clone): array
    {
        $this->variationValueUidMap = [];

        foreach ($source->variations as $variation) {
            if ($variation->is_global) {
                continue;
            }

            $newVariation = Variation::withoutEvents(function () use ($variation) {
                $model = new Variation([
                    'uid' => (string) Str::uuid(),
                    'type' => $variation->type,
                    'is_global' => false,
                    'position' => $variation->position,
                ]);

                foreach ($variation->translations as $translation) {
                    $model->translateOrNew($translation->locale)->name = $translation->name;
                }

                $model->save();

                return $model;
            });

            $clone->variations()->attach($newVariation->id);

            foreach ($variation->values as $value) {
                $newUid = (string) Str::uuid();
                $this->variationValueUidMap[$value->uid] = $newUid;

                $newValue = VariationValue::withoutEvents(function () use ($value, $newVariation, $newUid) {
                    $model = $newVariation->values()->create([
                        'uid' => $newUid,
                        'value' => $value->getRawOriginal('value'),
                        'position' => $value->position,
                    ]);

                    foreach ($value->translations as $translation) {
                        $model->translateOrNew($translation->locale)->label = $translation->label;
                    }

                    $model->save();

                    return $model;
                });

                if ($variation->type === 'image') {
                    $this->cloneEntityFiles($value, $newValue, ['media']);
                }
            }
        }

        return $this->variationValueUidMap;
    }

    private function cloneVariants(Product $source, Product $clone): void
    {
        foreach ($source->variants as $variant) {
            $attributes = collect($variant->getAttributes())
                ->only($variant->getFillable())
                ->except(['id', 'product_id', 'uid', 'uids'])
                ->all();

            $attributes['uid'] = (string) Str::uuid();
            $attributes['uids'] = $this->remapVariantUids($variant->uids);
            $attributes['name'] = $variant->getRawOriginal('name');

            if (! empty($attributes['sku'])) {
                $attributes['sku'] = $attributes['sku'] . '-copy';
            }

            $newVariant = ProductVariant::withoutEvents(function () use ($clone, $attributes) {
                return $clone->variants()->create($attributes);
            });

            $this->cloneEntityFiles($variant, $newVariant, ['base_image', 'additional_images']);
        }
    }

    private function remapVariantUids(?string $uids): ?string
    {
        if (empty($uids)) {
            return $uids;
        }

        return collect(explode('.', $uids))
            ->map(fn (string $uid) => $this->variationValueUidMap[$uid] ?? $uid)
            ->sort()
            ->values()
            ->implode('.');
    }

    private function cloneOptions(Product $source, Product $clone): void
    {
        foreach ($source->options as $option) {
            if ($option->is_global) {
                continue;
            }

            $newOption = Option::withoutEvents(function () use ($option) {
                $model = new Option([
                    'type' => $option->type,
                    'is_required' => $option->is_required,
                    'is_global' => false,
                    'position' => $option->position,
                ]);

                foreach ($option->translations as $translation) {
                    $model->translateOrNew($translation->locale)->name = $translation->name;
                }

                $model->save();

                return $model;
            });

            $clone->options()->attach($newOption->id);

            foreach ($option->values as $value) {
                OptionValue::withoutEvents(function () use ($value, $newOption) {
                    $model = $newOption->values()->create([
                        'price' => $value->getRawOriginal('price'),
                        'price_type' => $value->price_type,
                        'position' => $value->position,
                    ]);

                    foreach ($value->translations as $translation) {
                        $model->translateOrNew($translation->locale)->label = $translation->label;
                    }

                    $model->save();
                });
            }
        }
    }

    private function cloneProductRelations(Product $source, Product $clone): void
    {
        $clone->upSellProducts()->sync($source->upSellProducts()->pluck('id'));
        $clone->crossSellProducts()->sync($source->crossSellProducts()->pluck('id'));
        $clone->relatedProducts()->sync($source->relatedProducts()->pluck('id'));
    }

    /**
     * @param array<int, string> $zones
     */
    private function cloneEntityFiles($source, $target, array $zones): void
    {
        $files = [];

        foreach ($zones as $zone) {
            $fileIds = [];

            foreach ($source->filterFiles($zone)->get() as $file) {
                $duplicate = $this->duplicateFile($file);

                if ($duplicate) {
                    $fileIds[] = $duplicate->id;
                }
            }

            if ($fileIds !== []) {
                $files[$zone] = $fileIds;
            }
        }

        if ($files !== []) {
            $target->syncFiles($files);
        }
    }

    private function duplicateFile(File $file): ?File
    {
        $disk = Storage::disk($file->disk);
        $originalPath = $file->getRawOriginal('path');

        if (! $disk->exists($originalPath)) {
            return null;
        }

        $extension = $file->extension ?: pathinfo($originalPath, PATHINFO_EXTENSION) ?: 'jpg';
        $newPath = 'media/' . Str::uuid() . '.' . $extension;

        $disk->copy($originalPath, $newPath);

        return File::create([
            'user_id' => auth()->id() ?? $file->user_id,
            'disk' => $file->disk,
            'filename' => $file->filename,
            'path' => $newPath,
            'extension' => $extension,
            'mime' => $file->mime,
            'size' => $disk->size($newPath),
        ]);
    }
}
