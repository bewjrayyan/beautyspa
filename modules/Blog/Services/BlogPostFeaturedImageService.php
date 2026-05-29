<?php

namespace Modules\Blog\Services;

use Modules\Blog\Entities\BlogPost;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;

class BlogPostFeaturedImageService
{
    /** @var array<string, string> */
    private array $postProductMap = [
        'manfaat-aura-diamond-ad' => 'highway-premium-combo-drip',
        'manfaat-lumina-luxe' => 'anak-dara-combo-drip',
        'manfaat-pure-booster-vitamin-c' => '9-drip-laku-keras',
        'manfaat-snow-pearl-untuk-kulit' => 'anak-dara-combo-drip',
        'kekerapan-pengambilan-drip-menjaga-kesihatan-dan-kecantikan-anda' => 'highway-premium-combo-drip',
        'panduan-penjagaan-breastfiller-buttfiller-selepas-rawatan' => 'filler',
        'spa-treatment-manfaat-wellness-badan-dan-minda' => 'full-body-massage',
        'aesthetic-vs-medispa-cara-pilih-rawatan' => 'bio-facial-lifting',
        'skincare-profesional-vs-produk-rumah' => 'aura-seriputeh-body-bleaching',
        'hydra-facial-oxygen-facial-perbezaan' => 'hydra-facial-ala-carte',
        'pico-laser-vs-laser-biasa-pigmentasi' => 'pico-laser',
        'full-body-massage-manfaat-detoks-dan-tidur' => 'full-body-massage',
        'body-bleaching-bath-treatment-panduan' => 'aura-seriputeh-body-bleaching',
        'waxing-tips-kurangkan-ingrown-hair' => 'waxing-treatment',
        'eyelash-extension-brow-penjagaan' => 'eyelash-extension-ala-carte',
        'prp-rambut-rawatan-rambut-gugur' => 'hair-prp',
        'manicure-pedicure-spa-manfaat' => 'manicure',
        'bridal-package-timeline-rawatan-pengantin' => 'manicure',
        'botox-untuk-pemula-kegunaan-dan-keselamatan' => 'botox-smile-line-mata',
        'dermal-filler-panduan-dan-penjagaan' => 'filler',
        'thread-lift-benang-pdo-panduan' => 'benang-cog-double-chin',
        'iv-drip-therapy-nutrien-imun-dan-kulit' => '9-drip-laku-keras',
        'skin-booster-profhilo-hidrasi-dalam' => 'profilo-anti-aging',
        'suntikan-booster-whitening-selepas-sesi' => 'baby-booster-ala-carte',
        'ems-lipo-tanpa-bedah-cara-kerja' => 'ems-slimming-ala-carte',
        'whitening-booster-snow-pearl-vs-lumina-luxe' => 'anak-dara-combo-drip',
        'dimple-creation-prosedur-dan-pemulihan' => 'benang-muka-benang-pdo-d-chin-dimple',
        'pembedahan-kosmetik-vs-nonsurgical' => 'surgery',
        'skincare-routine-retinol-vitamin-c' => 'aura-seriputeh-body-bleaching',
        'makeup-selepas-facial-bila-selamat' => 'facial-cooling',
        'body-care-di-rumah-sambung-hasil-spa' => 'aura-seriputeh-body-bleaching',
        'hair-care-antara-sesi-prp' => 'hair-prp',
        'sun-protection-selepas-laser-dan-drip' => 'pico-laser',
        'aromaterapi-fragrance-dalam-spa' => 'full-body-massage',
        'alat-kecantikan-rumah-vs-profesional' => 'bio-facial-lifting',
    ];

    /** @var array<string, ?int> */
    private array $fileIdCache = [];

    public function assignToPost(BlogPost $post, bool $force = false): bool
    {
        $post->loadMissing('category', 'files');

        if (! $force && optional($post->featured_image)->id) {
            return false;
        }

        $fileId = $this->resolveFileId($post);

        if (! $fileId) {
            return false;
        }

        $post->syncFiles(['featured_image' => [$fileId]]);

        return true;
    }

    /**
     * @return array{assigned: int, skipped: int, failed: int}
     */
    public function assignAll(bool $force = false): array
    {
        $assigned = 0;
        $skipped = 0;
        $failed = 0;

        BlogPost::withoutGlobalScope('published')
            ->with(['category', 'files'])
            ->orderBy('id')
            ->get()
            ->each(function (BlogPost $post) use ($force, &$assigned, &$skipped, &$failed) {
                if ($this->assignToPost($post, $force)) {
                    $assigned++;
                } elseif (optional($post->fresh()->featured_image)->id) {
                    $skipped++;
                } else {
                    $failed++;
                }
            });

        return compact('assigned', 'skipped', 'failed');
    }

    private function resolveFileId(BlogPost $post): ?int
    {
        $slug = $post->slug;

        if (isset($this->postProductMap[$slug])) {
            $fileId = $this->fileIdForProductSlug($this->postProductMap[$slug]);

            if ($fileId) {
                return $fileId;
            }
        }

        $fileId = $this->fileIdFromSlugKeywords($slug);

        if ($fileId) {
            return $fileId;
        }

        $categorySlug = $post->category?->slug;

        if ($categorySlug) {
            return $this->fileIdForCategorySlug($categorySlug);
        }

        return null;
    }

    private function fileIdFromSlugKeywords(string $postSlug): ?int
    {
        $keywords = ['hydra-facial', 'oxygen-facial', 'pico-laser', 'laser-hair', 'hair-prp', 'prp', 'botox', 'filler', 'benang', 'dimple', 'lipo', 'ems', 'waxing', 'massage', 'eyelash', 'manicure', 'injection', 'profilo', 'pigment', 'facial', 'drip', 'booster', 'highway', 'anak-dara'];

        foreach ($keywords as $keyword) {
            if (! str_contains($postSlug, $keyword)) {
                continue;
            }

            $product = Product::where('slug', 'like', '%' . $keyword . '%')
                ->orderBy('id')
                ->get()
                ->first(fn (Product $product) => optional($product->base_image)->id);

            if ($product) {
                return $this->cacheFileId($product);
            }
        }

        return null;
    }

    private function fileIdForProductSlug(string $productSlug): ?int
    {
        if (isset($this->fileIdCache['product:' . $productSlug])) {
            return $this->fileIdCache['product:' . $productSlug];
        }

        $product = Product::where('slug', $productSlug)->first();

        if (! $product) {
            $product = Product::where('slug', 'like', $productSlug . '%')->first();
        }

        return $product ? $this->cacheFileId($product) : null;
    }

    private function fileIdForCategorySlug(string $categorySlug): ?int
    {
        if (array_key_exists('category:' . $categorySlug, $this->fileIdCache)) {
            return $this->fileIdCache['category:' . $categorySlug];
        }

        $category = Category::where('slug', $categorySlug)->first();

        if (! $category) {
            return null;
        }

        $fileId = $this->firstProductFileIdInCategories([$category->id]);

        if (! $fileId && $category->parent_id) {
            $fileId = $this->firstProductFileIdInCategories([$category->parent_id]);
        }

        if (! $fileId) {
            $childIds = Category::where('parent_id', $category->id)->pluck('id')->all();

            if ($childIds !== []) {
                $fileId = $this->firstProductFileIdInCategories($childIds);
            }
        }

        $this->fileIdCache['category:' . $categorySlug] = $fileId;

        return $fileId;
    }

    /**
     * @param array<int, int> $categoryIds
     */
    private function firstProductFileIdInCategories(array $categoryIds): ?int
    {
        $product = Product::query()
            ->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $categoryIds))
            ->orderBy('id')
            ->get()
            ->first(fn (Product $item) => optional($item->base_image)->id);

        return $product ? $this->cacheFileId($product) : null;
    }

    private function cacheFileId(Product $product): ?int
    {
        $product->loadMissing('files');
        $fileId = optional($product->base_image)->id;

        if ($fileId) {
            $this->fileIdCache['product:' . $product->slug] = $fileId;
        }

        return $fileId;
    }
}
