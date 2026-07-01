<?php

namespace Modules\Product\Services;

use finfo;
use Illuminate\Support\Str;
use Modules\Media\Entities\File;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Tag\Entities\Tag;
use Modules\Category\Entities\Category;
use Modules\Variation\Entities\Variation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImmaSeriLarisTreatmentImporter
{
    private const BASE_URL = 'https://immaserilaris.com';

    private const MAX_ADDITIONAL_IMAGES = 12;

    private const LOCAL_IMAGE_BASE = __DIR__ . '/../Data/images/birthday-founder-mega-sale-2026';

    private array $imageCache = [];

    private array $categoryMap = [];

    private bool $physicalStock = false;

    private int $stockQty = 100;

    /** @var callable|null */
    private $logger;

    public function __construct(?callable $logger = null)
    {
        $this->logger = $logger;
        $this->categoryMap = $this->buildCategoryMap();
    }


    public function syncVariantsForExisting(?callable $logger = null): array
    {
        if ($logger) {
            $this->logger = $logger;
        }

        $stats = ['updated' => 0, 'skipped' => 0, 'failed' => 0];

        $slugs = collect($this->collectProductUrls())
            ->map(fn ($url) => Str::afterLast(trim(parse_url($url, PHP_URL_PATH), '/'), '/'))
            ->filter()
            ->values()
            ->all();

        Product::withoutGlobalScope('active')
            ->whereIn('slug', $slugs)
            ->orderBy('id')
            ->chunk(20, function ($products) use (&$stats) {
                foreach ($products as $product) {
                    $url = self::BASE_URL . '/product/' . $product->slug . '/';
                    $data = $this->scrapeProduct($url);

                    if (empty($data['variations'])) {
                        $stats['skipped']++;

                        continue;
                    }

                    try {
                        DB::transaction(function () use ($product, $data) {
                            $product->variants()->forceDelete();
                            $product->variations()->detach();

                            $this->importVariants($product, $data['variations'], true);
                        });

                        $stats['updated']++;
                        $this->log('  Variants synced: ' . $product->name);
                    } catch (\Throwable $e) {
                        $stats['failed']++;
                        $this->log('  Variant sync failed (' . $product->slug . '): ' . $e->getMessage());
                    }
                }
            });

        return $stats;
    }


    /**
     * Import a product from a static catalog entry (e.g. promo flyer data).
     *
     * @param array<string, mixed> $entry
     */
    public function importFromData(array $entry, bool $skipImages = false, bool $force = false): bool
    {
        $variations = $entry['variations'] ?? [];

        [$price, $specialPrice] = $this->extractPrices('', $variations);

        $data = [
            'slug' => $entry['slug'],
            'sku' => $entry['sku'] ?? strtoupper(Str::slug($entry['slug'], '_')),
            'name' => $entry['name'],
            'description' => $entry['description'] ?? '',
            'short_description' => $entry['short_description'] ?? Str::limit(strip_tags($entry['description'] ?? ''), 200),
            'tags' => $entry['tags'] ?? ['Promo'],
            'images' => $entry['images'] ?? [],
            'local_images' => $entry['local_images'] ?? [],
            'variations' => $variations,
            'price' => $price,
            'special_price' => $specialPrice,
        ];

        if (empty($data['name'])) {
            return false;
        }

        $existing = $this->findProductForImport($data);

        if ($existing) {
            if (! $force) {
                $this->log('  Skipped (exists): ' . $existing->slug);

                return false;
            }

            DB::transaction(function () use ($existing, $data, $skipImages) {
                $this->updateExistingProduct($existing->fresh(), $data, $skipImages);
            });

            return true;
        }

        return DB::transaction(function () use ($data, $skipImages) {
            return $this->persistProduct($data, $data['slug'], $skipImages);
        });
    }


    public function importAll(int $limit = 0, bool $skipImages = false): array
    {
        $urls = $this->collectProductUrls();
        $stats = ['total' => count($urls), 'imported' => 0, 'skipped' => 0, 'failed' => 0];

        if ($limit > 0) {
            $urls = array_slice($urls, 0, $limit);
        }

        foreach ($urls as $index => $url) {
            $this->log(sprintf('[%d/%d] %s', $index + 1, count($urls), $url));

            try {
                if ($this->importProductUrl($url, $skipImages)) {
                    $stats['imported']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Throwable $e) {
                $stats['failed']++;
                $this->log('  ERROR: ' . $e->getMessage());
            }
        }

        return $stats;
    }


    public function collectProductUrls(): array
    {
        $urls = [];

        foreach ([self::BASE_URL . '/treatment/', self::BASE_URL . '/treatment/page/2/'] as $listingUrl) {
            $html = $this->fetch($listingUrl);

            if (! $html) {
                continue;
            }

            preg_match_all(
                '#href="(https://immaserilaris\.com/product/[^"]+/)#',
                $html,
                $matches
            );

            foreach ($matches[1] as $url) {
                $urls[$url] = $url;
            }
        }

        return array_values($urls);
    }


    public function importProductUrl(
        string $url,
        bool $skipImages = false,
        bool $force = false,
        bool $physicalStock = false,
        int $stockQty = 100
    ): bool {
        $this->physicalStock = $physicalStock;
        $this->stockQty = max(0, $stockQty);

        $data = $this->scrapeProduct($url);

        if (empty($data['name'])) {
            return false;
        }

        $existing = $this->findProductForImport($data);

        if ($existing) {
            if ($force) {
                return $this->refreshProductUrl($url, $skipImages, $physicalStock, $stockQty);
            }

            $this->log('  Skipped (exists): ' . $existing->slug);

            return false;
        }

        return DB::transaction(function () use ($data, $skipImages) {
            return $this->persistProduct($data, $data['slug'], $skipImages);
        });
    }


    public function refreshProductUrl(
        string $url,
        bool $skipImages = false,
        bool $physicalStock = false,
        int $stockQty = 100
    ): bool {
        $this->physicalStock = $physicalStock;
        $this->stockQty = max(0, $stockQty);

        $data = $this->scrapeProduct($url);

        if (empty($data['name'])) {
            return false;
        }

        $product = $this->findProductForImport($data);

        if (! $product) {
            return DB::transaction(function () use ($data, $skipImages) {
                return $this->persistProduct($data, $data['slug'], $skipImages);
            });
        }

        DB::transaction(function () use ($product, $data, $skipImages) {
            $this->ensureCanonicalSlug($product, $data['slug']);
            $this->purgeDuplicateProducts($product, $data['name']);
            $this->updateExistingProduct($product->fresh(), $data, $skipImages);
        });

        return true;
    }


    /**
     * @param array<string, mixed> $data
     */
    private function findProductForImport(array $data): ?Product
    {
        $product = Product::withoutGlobalScope('active')
            ->withTrashed()
            ->where('slug', $data['slug'])
            ->first();

        if ($product || empty($data['name'])) {
            return $product;
        }

        return Product::withoutGlobalScope('active')
            ->withTrashed()
            ->whereTranslation('name', $data['name'])
            ->orderBy('id')
            ->first();
    }


    private function ensureCanonicalSlug(Product $product, string $slug): void
    {
        if ($product->slug !== $slug) {
            $product->updateQuietly(['slug' => $slug]);
        }
    }


    private function purgeDuplicateProducts(Product $keep, string $name): void
    {
        $duplicates = Product::withoutGlobalScope('active')
            ->where('id', '!=', $keep->id)
            ->whereTranslation('name', $name)
            ->get();

        foreach ($duplicates as $duplicate) {
            $this->deleteProductPermanently($duplicate);
        }
    }


    /**
     * Remove all duplicate products (same name or random slug suffix when canonical exists).
     *
     * @return array{removed: int, by_name: int, by_slug_suffix: int}
     */
    public function purgeAllDuplicateProducts(): array
    {
        $stats = ['removed' => 0, 'by_name' => 0, 'by_slug_suffix' => 0];
        $removedIds = [];

        $products = Product::withoutGlobalScope('active')->with('translations')->get();
        $byName = [];

        foreach ($products as $product) {
            $name = mb_strtolower(trim($product->name));

            if ($name === '') {
                continue;
            }

            $byName[$name] = $byName[$name] ?? [];
            $byName[$name][] = $product;
        }

        foreach ($byName as $group) {
            if (count($group) < 2) {
                continue;
            }

            usort($group, fn ($a, $b) => $this->duplicateKeepScore($b) <=> $this->duplicateKeepScore($a));
            $keep = array_shift($group);

            foreach ($group as $duplicate) {
                if (in_array($duplicate->id, $removedIds, true)) {
                    continue;
                }

                $this->deleteProductPermanently($duplicate);
                $removedIds[] = $duplicate->id;
                $stats['removed']++;
                $stats['by_name']++;
            }
        }

        $suffixPattern = '/-[a-zA-Z0-9]{8}$/';

        foreach (Product::withoutGlobalScope('active')->get() as $product) {
            if (in_array($product->id, $removedIds, true)) {
                continue;
            }

            if (! preg_match($suffixPattern, $product->slug)) {
                continue;
            }

            $baseSlug = preg_replace($suffixPattern, '', $product->slug);
            $canonical = Product::withoutGlobalScope('active')
                ->where('slug', $baseSlug)
                ->where('id', '!=', $product->id)
                ->first();

            if (! $canonical) {
                continue;
            }

            $this->deleteProductPermanently($product);
            $removedIds[] = $product->id;
            $stats['removed']++;
            $stats['by_slug_suffix']++;
        }

        return $stats;
    }


    private function duplicateKeepScore(Product $product): int
    {
        return ($product->files()->count() * 100)
            + ($product->variants()->count() * 10)
            + ($product->is_active ? 5 : 0)
            - (int) ($product->id / 100000);
    }


    private function deleteProductPermanently(Product $product): void
    {
        $product->variants()->forceDelete();
        $product->variations()->detach();

        DB::table('entity_files')
            ->where('entity_type', Product::class)
            ->where('entity_id', $product->id)
            ->delete();

        $product->forceDelete();

        $this->log('  Removed duplicate: ' . $product->slug);
    }


    /**
     * Remove stray birthday mega sale products (per-option duplicates, wrong slugs).
     *
     * @param array<int, string> $canonicalSlugs
     */
    public function removeBirthdayMegaSaleOrphans(array $canonicalSlugs): int
    {
        $removed = 0;
        $canonical = array_flip($canonicalSlugs);

        Product::withoutGlobalScope('active')
            ->where('slug', 'like', '%birthday-founder-mega-sale%')
            ->orderBy('id')
            ->get()
            ->each(function (Product $product) use ($canonical, &$removed) {
                if (isset($canonical[$product->slug])) {
                    return;
                }

                $this->deleteProductPermanently($product);
                $removed++;
            });

        return $removed;
    }


    private function updateExistingProduct(Product $product, array $data, bool $skipImages): void
    {
        if ($product->trashed()) {
            $product->restore();
        }

        $categoryIds = $this->resolveCategoryIds($data['tags']);
        $tagIds = $this->resolveTagIds($data['tags']);

        $product->updateQuietly(array_merge([
            'sku' => ($data['sku'] ?? null) ?: $product->sku,
            'price' => $data['price'],
            'special_price' => $data['special_price'],
            'special_price_type' => $data['special_price'] ? 'fixed' : null,
            'is_active' => true,
            'in_stock' => true,
        ], $this->stockAttributes()));

        $this->syncProductSellingPrice($product);

        $this->updateProductTranslations($product, $data);

        if ($categoryIds) {
            $product->categories()->sync($categoryIds);
        }

        if ($tagIds) {
            $product->tags()->sync($tagIds);
        }

        $this->attachCatalogImages($product, $data, $skipImages);

        $product->variants()->forceDelete();
        $product->variations()->detach();

        if (! empty($data['variations'])) {
            $this->importVariants($product, $data['variations'], $skipImages);
        }

        $product->saveMetaData([
            'meta_title' => $data['name'] . ' | IMMA Seri Laris',
            'meta_description' => Str::limit(strip_tags($data['short_description']), 160),
        ]);

        $this->log('  Refreshed: ' . $data['name']);
    }


    private function persistProduct(array $data, string $slug, bool $skipImages): bool
    {
        $categoryIds = $this->resolveCategoryIds($data['tags']);
        $tagIds = $this->resolveTagIds($data['tags']);

        Product::unguard();

        $product = Product::withoutGlobalScope('active')->create(array_merge([
            'slug' => $slug,
            'sku' => $data['sku'] ?? strtoupper(Str::slug($slug, '_')),
            'price' => $data['price'],
            'special_price' => $data['special_price'],
            'special_price_type' => $data['special_price'] ? 'fixed' : null,
            'is_active' => true,
            'en' => [
                'name' => $data['name'],
                'description' => $data['description'],
                'short_description' => $data['short_description'],
            ],
            'ms' => [
                'name' => $data['name'],
                'description' => $data['description'],
                'short_description' => $data['short_description'],
            ],
        ], $this->stockAttributes()));

        Product::reguard();

        if ($categoryIds) {
            $product->categories()->sync($categoryIds);
        }

        if ($tagIds) {
            $product->tags()->sync($tagIds);
        }

        $this->attachCatalogImages($product, $data, $skipImages);

        if (! empty($data['variations'])) {
            $this->importVariants($product, $data['variations'], $skipImages);
        }

        $product->saveMetaData([
            'meta_title' => $data['name'] . ' | IMMA Seri Laris',
            'meta_description' => Str::limit(strip_tags($data['short_description']), 160),
        ]);

        $this->log('  Imported: ' . $data['name']);

        return true;
    }


    /**
     * @param array<string, mixed> $data
     */
    private function attachCatalogImages(Product $product, array $data, bool $skipImages): void
    {
        if ($skipImages) {
            return;
        }

        if (! empty($data['local_images'])) {
            $this->attachProductLocalImages($product, $data['local_images']);

            return;
        }

        if (! empty($data['images'])) {
            $this->attachProductImages($product, $data['images']);
        }
    }


    /**
     * @param array<int, string> $filenames
     */
    private function attachProductLocalImages(Product $product, array $filenames): void
    {
        DB::table('entity_files')
            ->where('entity_type', Product::class)
            ->where('entity_id', $product->id)
            ->delete();

        $baseId = null;
        $additional = [];

        foreach ($filenames as $filename) {
            $fileId = $this->storeLocalImage($filename);

            if (! $fileId) {
                continue;
            }

            if ($baseId === null) {
                $baseId = $fileId;
            } else {
                $additional[] = $fileId;
            }

            if (count($additional) >= self::MAX_ADDITIONAL_IMAGES) {
                break;
            }
        }

        $files = [];

        if ($baseId) {
            $files['base_image'] = [$baseId];
        }

        if ($additional) {
            $files['additional_images'] = $additional;
        }

        if ($files) {
            $this->syncEntityFiles(Product::class, $product->id, $files);
        }
    }


    private function storeLocalImage(string $filename): ?int
    {
        $filename = ltrim($filename, '/');
        $absolutePath = self::LOCAL_IMAGE_BASE . '/' . $filename;

        if (isset($this->imageCache[$absolutePath])) {
            return $this->imageCache[$absolutePath];
        }

        if (! is_readable($absolutePath)) {
            return null;
        }

        $content = file_get_contents($absolutePath);

        if ($content === false) {
            return null;
        }

        $pathInfo = pathinfo($absolutePath);
        $extension = $pathInfo['extension'] ?? 'jpg';
        $storageFilename = 'imma-' . Str::slug($pathInfo['filename'] ?? Str::random(8)) . '.' . $extension;
        $storagePath = 'media/' . $storageFilename;

        if (! Storage::disk(config('filesystems.default'))->put($storagePath, $content)) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);

        $file = File::create([
            'user_id' => 1,
            'disk' => config('filesystems.default'),
            'filename' => substr($storageFilename, 0, 255),
            'path' => $storagePath,
            'extension' => $extension,
            'mime' => $mime ?: 'image/jpeg',
            'size' => strlen($content),
        ]);

        return $this->imageCache[$absolutePath] = $file->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function scrapeProduct(string $url): array
    {
        $url = preg_replace('/#.*$/', '', $url) ?? $url;
        $html = $this->fetch($url) ?? '';
        $slug = trim(parse_url($url, PHP_URL_PATH), '/');
        $slug = Str::afterLast($slug, '/');

        $name = $this->matchGroup(
            $html,
            '/<h1[^>]*class="[^"]*product_title[^"]*"[^>]*>(.*?)<\/h1>/s'
        ) ?: $this->matchGroup($html, '/<h1[^>]*>(.*?)<\/h1>/s');

        $name = html_entity_decode(strip_tags($name), ENT_QUOTES, 'UTF-8');

        $short = $this->extractShortDescription($html);
        $long = $this->extractLongDescription($html);
        $description = $this->buildFullDescription($short, $long);
        $shortDescription = $short !== '' ? $short : Str::limit(strip_tags($description), 200);

        $tags = $this->extractTags($html);
        $images = $this->extractImages($html);
        $variations = $this->extractVariations($html);

        [$price, $specialPrice] = $this->extractPrices($html, $variations);

        return [
            'slug' => $slug,
            'sku' => $this->matchGroup($html, '/sku["\']?\s*:\s*["\']([^"\']+)/'),
            'name' => trim($name),
            'description' => $description,
            'short_description' => $shortDescription,
            'tags' => $tags,
            'images' => $images,
            'variations' => $variations,
            'price' => $price,
            'special_price' => $specialPrice,
        ];
    }


    private function extractShortDescription(string $html): string
    {
        $block = $this->matchGroup(
            $html,
            '/woocommerce-product-details__short-description[^>]*>(.*?)<\/div>/s'
        );

        return $this->cleanHtml($block);
    }


    private function extractLongDescription(string $html): string
    {
        $block = '';

        if (preg_match(
            '/id="tab-description"[^>]*>(.*?)(?=<div[^>]+id="tab-additional_information"|<div[^>]+class="[^"]*woocommerce-Tabs-panel--additional_information)/s',
            $html,
            $match
        )) {
            $block = $match[1];
        }

        if ($block === '' && preg_match('/id="tab-description"[^>]*>(.*)/s', $html, $match)) {
            $rest = $match[1];
            $endMarkers = [
                'id="tab-additional_information"',
                'woocommerce-Tabs-panel--additional_information',
                'id="tab-my_custom_tab"',
                'woocommerce-Tabs-panel--my_custom_tab',
            ];

            $end = strlen($rest);

            foreach ($endMarkers as $marker) {
                $pos = strpos($rest, $marker);

                if ($pos !== false && $pos < $end) {
                    $end = $pos;
                }
            }

            $block = substr($rest, 0, $end);
        }

        if ($block === '') {
            $block = $this->matchGroup(
                $html,
                '/woocommerce-Tabs-panel--description[^>]*>(.*?)<\/div>\s*<\/div>\s*<div class="woocommerce-Tabs-panel/s'
            );
        }

        $block = $this->sanitizeDescription($block);
        $block = preg_replace(
            '#<table class="woocommerce-product-attributes[^>]*>.*?</table>#is',
            '',
            $block
        ) ?? $block;

        return $this->cleanHtml($block);
    }


    private function buildFullDescription(string $short, string $long): string
    {
        $short = trim($short);
        $long = trim($long);

        if ($long === '') {
            return $short;
        }

        if ($short === '') {
            return $long;
        }

        $shortPlain = trim(strip_tags($short));

        if ($shortPlain !== '' && str_contains(strip_tags($long), $shortPlain)) {
            return $long;
        }

        return $short . '<br><br>' . $long;
    }


    private function updateProductTranslations(Product $product, array $data): void
    {
        $payload = [
            'name' => $data['name'],
            'description' => $data['description'],
            'short_description' => $data['short_description'],
        ];

        foreach (['en', 'ms'] as $locale) {
            DB::table('product_translations')->updateOrInsert(
                [
                    'product_id' => $product->id,
                    'locale' => $locale,
                ],
                $payload
            );
        }

        $product->load('translations');
    }


    private function sanitizeDescription(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $html = $this->resolveLazyImages($html);
        $html = preg_replace('#<noscript>.*?</noscript>#is', '', $html);
        $html = preg_replace('#<img[^>]+src="data:image/svg\+xml[^"]*"[^>]*>#i', '', $html);
        $html = preg_replace('#<div class="wp-gr\b.*?</div>#is', '', $html);
        $html = preg_replace('#<svg xmlns.*?</svg>#is', '', $html);
        $html = preg_replace('#<h2>\s*Testimoni\s*</h2>.*$#is', '', $html);
        $html = preg_replace('#<h2>\s*Description\s*</h2>#i', '', $html);
        $html = preg_replace('#<h2>\s*Additional information\s*</h2>#i', '', $html);
        $html = preg_replace('#<h3[^>]*>\s*</h3>#i', '', $html);

        return trim($html);
    }


    private function resolveLazyImages(string $html): string
    {
        return preg_replace_callback(
            '/<img\b([^>]*?)>/is',
            function (array $matches): string {
                $attrs = $matches[1];

                if (! preg_match('/data-lazy-src="([^"]+)"/i', $attrs, $lazy)) {
                    return '<img' . $attrs . '>';
                }

                $src = $lazy[1];

                if (preg_match('/\ssrc="[^"]*"/i', $attrs)) {
                    $attrs = preg_replace('/\ssrc="[^"]*"/i', ' src="' . $src . '"', $attrs);
                } else {
                    $attrs .= ' src="' . $src . '"';
                }

                $attrs = preg_replace('/\sdata-lazy-src="[^"]*"/i', '', $attrs);
                $attrs = preg_replace('/\sdata-lazy-srcset="[^"]*"/i', '', $attrs);
                $attrs = preg_replace('/\sdata-lazy-sizes="[^"]*"/i', '', $attrs);

                return '<img' . $attrs . '>';
            },
            $html
        ) ?? $html;
    }


    private function extractTags(string $html): array
    {
        preg_match_all(
            '/rel="tag"[^>]*>([^<]+)</',
            $html,
            $matches
        );

        $tags = array_map(fn ($t) => trim(html_entity_decode($t)), $matches[1] ?? []);

        return array_values(array_unique(array_filter($tags)));
    }


    private function extractImages(string $html): array
    {
        $images = [];

        preg_match_all(
            '/<div[^>]*class="[^"]*woocommerce-product-gallery__image[^"]*"[^>]*>(.*?)<\/div>/s',
            $html,
            $slides
        );

        foreach ($slides[1] ?? [] as $slideHtml) {
            $this->pushGallerySlideImage($images, $slideHtml);
        }

        if (empty($images)) {
            $searchIn = '';

            if (preg_match(
                '/<div[^>]*class="[^"]*woocommerce-product-gallery[^"]*"[^>]*>(.*?)(?:<div[^>]*class="[^"]*summary[^"]*entry-summary|<div class="woocommerce-product-gallery__trigger)/s',
                $html,
                $gallery
            )) {
                $searchIn = $gallery[1];
            }

            if ($searchIn !== '') {
                preg_match_all(
                    '#data-large_image="([^"]+)"#i',
                    $searchIn,
                    $largeMatches
                );

                foreach ($largeMatches[1] ?? [] as $url) {
                    $this->pushImageUrl($images, $url);
                }

                preg_match_all(
                    '#(?:data-lazy-src|data-src|src|href)="([^"]+)"#i',
                    $searchIn,
                    $attrMatches
                );

                foreach ($attrMatches[1] ?? [] as $url) {
                    $this->pushImageUrl($images, $url);
                }
            }
        }

        if (empty($images) && preg_match('/property="og:image"[^>]+content="([^"]+)"/', $html, $m)) {
            $this->pushImageUrl($images, $m[1]);
        }

        return array_values(array_unique($images));
    }


    private function pushGallerySlideImage(array &$images, string $slideHtml): void
    {
        foreach (['data-large_image', 'data-src', 'data-lazy-src'] as $attribute) {
            if (preg_match('#' . $attribute . '="([^"]+)"#i', $slideHtml, $match)) {
                $this->pushImageUrl($images, $match[1]);

                return;
            }
        }

        if (preg_match('#<a[^>]+href="([^"]+)"#i', $slideHtml, $match)) {
            $this->pushImageUrl($images, $match[1]);
        }
    }


    /**
     * @param array<int, string> $images
     */
    private function pushImageUrl(array &$images, ?string $url): void
    {
        $url = $this->normalizeImageUrl($url);

        if ($url === '') {
            return;
        }

        $lower = strtolower($url);

        if (
            str_contains($lower, 'logo')
            || str_contains($lower, 'youtube')
            || str_contains($lower, 'favicon')
            || str_contains($lower, 'cropped-')
            || ! preg_match('#/wp-content/uploads/.+\.(?:jpe?g|png|webp)$#i', $url)
        ) {
            return;
        }

        $images[] = $url;
    }


    private function normalizeImageUrl(?string $url): string
    {
        if (! $url) {
            return '';
        }

        $url = html_entity_decode(trim($url), ENT_QUOTES, 'UTF-8');
        $url = strtok($url, '?') ?: $url;

        if (preg_match('#^https?://i\d+\.wp\.com/(.+)$#i', $url, $match)) {
            $url = 'https://' . $match[1];
        }

        if (! str_starts_with($url, 'http')) {
            if (str_starts_with($url, '//')) {
                $url = 'https:' . $url;
            } elseif (str_starts_with($url, '/')) {
                $url = self::BASE_URL . $url;
            }
        }

        return preg_replace('/-\d+x\d+(\.[^.\/]+)$/i', '$1', $url) ?? $url;
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractVariations(string $html): array
    {
        if (! preg_match('/data-product_variations="([^"]+)"/', $html, $m)) {
            return [];
        }

        $json = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        $data = json_decode($json, true);

        if (! is_array($data)) {
            return [];
        }

        $variations = [];

        foreach ($data as $row) {
            $label = '';

            if (! empty($row['attributes']) && is_array($row['attributes'])) {
                $label = (string) reset($row['attributes']);
            }

            if ($label === '') {
                continue;
            }

            $imageUrl = $row['image']['full_src'] ?? $row['image']['url'] ?? null;

            $variations[] = [
                'label' => html_entity_decode($label, ENT_QUOTES, 'UTF-8'),
                'price' => (float) ($row['display_price'] ?? 0),
                'regular_price' => (float) ($row['display_regular_price'] ?? 0),
                'image' => $imageUrl,
                'sku' => $row['sku'] ?? null,
            ];
        }

        return $variations;
    }


    /**
     * @param array<int, array<string, mixed>> $variations
     * @return array{0: float, 1: ?float}
     */
    private function extractPrices(string $html, array $variations): array
    {
        if ($variations) {
            $prices = array_column($variations, 'price');
            $regular = array_column($variations, 'regular_price');
            $min = min($prices);
            $minRegular = min(array_filter($regular) ?: $regular);

            return [
                $minRegular > $min ? $minRegular : $min,
                $minRegular > $min ? $min : null,
            ];
        }

        $summaryPrices = $this->extractPricesFromSummary($html);

        if ($summaryPrices !== null) {
            return $summaryPrices;
        }

        $amounts = $this->parsePriceAmounts($html);

        if (empty($amounts)) {
            return [0, null];
        }

        $price = min($amounts);
        $del = $this->matchGroup(
            html_entity_decode($html, ENT_QUOTES, 'UTF-8'),
            '/<del[^>]*>.*?([\d,]+\.?\d*)/s'
        );

        if ($del) {
            $regular = (float) str_replace(',', '', strip_tags($del));

            if ($regular > $price) {
                return [$regular, $price];
            }
        }

        return [$price, null];
    }


    /**
     * @return array{0: float, 1: ?float}|null
     */
    private function extractPricesFromSummary(string $html): ?array
    {
        $block = '';

        if (preg_match_all('/<p class="price"[^>]*>(.*?)<\/p>/s', $html, $priceMatches) && ! empty($priceMatches[1])) {
            $block = $priceMatches[1][0];
        }

        if ($block === '') {
            $block = $this->matchGroup(
                $html,
                '/class="[^"]*entry-summary[^"]*"[^>]*>(.*?)<div class="woocommerce-tabs/s'
            );
        }

        if ($block === '') {
            return null;
        }

        $amounts = $this->parsePriceAmounts($block);

        if (empty($amounts)) {
            return null;
        }

        $price = min($amounts);
        $decodedBlock = html_entity_decode($block, ENT_QUOTES, 'UTF-8');

        if (preg_match('/<del[^>]*>.*?([\d,]+\.?\d*)/s', $decodedBlock, $delMatch)) {
            $regular = (float) str_replace(',', '', strip_tags($delMatch[1]));

            if ($regular > $price) {
                return [$regular, $price];
            }
        }

        return [$price, null];
    }


    /**
     * @return array<int, float>
     */
    private function parsePriceAmounts(string $html): array
    {
        $decoded = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
        $text = strip_tags($decoded);
        $amounts = [];

        if (preg_match_all('/(?:RM\s*)?([\d,]+\.\d{2})/', $text, $matches)) {
            foreach ($matches[1] as $raw) {
                $amounts[] = (float) str_replace(',', '', $raw);
            }
        }

        return array_values(array_unique($amounts));
    }


    /**
     * @param array<int, array<string, mixed>> $wcVariations
     */
    private function importVariants(Product $product, array $wcVariations, bool $skipImages): void
    {
        $variation = Variation::create([
            'uid' => (string) Str::uuid(),
            'type' => 'text',
            'is_global' => false,
            'position' => 1,
            'en' => ['name' => 'Treatment'],
            'ms' => ['name' => 'Rawatan'],
        ]);

        $product->variations()->attach($variation->id);

        $valueRows = [];
        $position = 0;

        foreach ($wcVariations as $row) {
            $position++;
            $valueUid = (string) Str::uuid();
            $valueRows[] = [
                'uid' => $valueUid,
                'label' => $row['label'],
                'position' => $position,
            ];
        }

        $variation->saveValues($valueRows);

        $valueUidByLabel = [];

        foreach ($valueRows as $row) {
            $valueUidByLabel[$row['label']] = $row['uid'];
        }

        foreach ($wcVariations as $index => $row) {
            $valueUid = $valueUidByLabel[$row['label']] ?? (string) Str::uuid();
            $uids = $valueUid;
            $price = $row['regular_price'] > $row['price']
                ? $row['regular_price']
                : $row['price'];
            $special = $row['regular_price'] > $row['price']
                ? $row['price']
                : null;

            $variant = $product->variants()->withoutGlobalScope('active')->create(array_merge([
                'uid' => $valueUid,
                'uids' => $uids,
                'name' => $row['label'],
                'sku' => $row['sku'] ?? null,
                'price' => $price,
                'special_price' => $special,
                'special_price_type' => $special ? 'fixed' : null,
                'is_active' => true,
                'is_default' => false,
                'position' => $index + 1,
            ], $this->variantStockAttributes()));

            if (! $skipImages && ! empty($row['image'])) {
                $fileId = $this->downloadImage($row['image']);

                if ($fileId) {
                    $this->syncEntityFiles(ProductVariant::class, $variant->id, [
                        'base_image' => [$fileId],
                    ]);
                }
            }
        }

        $minPrice = $product->variants()
            ->withoutGlobalScope('active')
            ->min('price');

        if ($minPrice !== null) {
            // Avoid Product::saved listeners (admin form handlers) wiping CLI-imported variants.
            $product->updateQuietly(['price' => $minPrice]);
            $this->syncProductSellingPrice($product->fresh());
        }
    }


    private function syncProductSellingPrice(Product $product): void
    {
        $product->updateQuietly([
            'selling_price' => ($product->hasSpecialPrice()
                ? $product->getSpecialPrice()
                : $product->price)->amount(),
        ]);
    }


    /**
     * @param array<int, string> $urls
     */
    private function attachProductImages(Product $product, array $urls): void
    {
        DB::table('entity_files')
            ->where('entity_type', Product::class)
            ->where('entity_id', $product->id)
            ->delete();

        $baseId = null;
        $additional = [];

        $urls = $this->preferFullSizeImages($urls);

        foreach (array_values(array_unique($urls)) as $index => $url) {
            $fileId = $this->downloadImage($url);

            if (! $fileId) {
                continue;
            }

            if ($baseId === null) {
                $baseId = $fileId;
            } else {
                $additional[] = $fileId;
            }

            if (count($additional) >= self::MAX_ADDITIONAL_IMAGES) {
                break;
            }
        }

        $files = [];

        if ($baseId) {
            $files['base_image'] = [$baseId];
        }

        if ($additional) {
            $files['additional_images'] = $additional;
        }

        if ($files) {
            $this->syncEntityFiles(Product::class, $product->id, $files);
        }
    }


    /**
     * @param array<int, string> $urls
     * @return array<int, string>
     */
    private function preferFullSizeImages(array $urls): array
    {
        $byStem = [];

        foreach ($urls as $url) {
            $normalized = $this->normalizeImageUrl($url);
            $stem = preg_replace('/-\d+x\d+(\.[^.\/]+)$/i', '$1', $normalized) ?? $normalized;
            $stemKey = strtolower($stem);

            if (! isset($byStem[$stemKey]) || strlen($normalized) > strlen($byStem[$stemKey])) {
                $byStem[$stemKey] = $normalized;
            }
        }

        return array_values($byStem);
    }


    private function downloadImage(string $url): ?int
    {
        $url = $this->normalizeImageUrl($url);

        if (isset($this->imageCache[$url])) {
            return $this->imageCache[$url];
        }

        $content = @file_get_contents($url);

        if ($content === false) {
            return null;
        }

        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $extension = $pathInfo['extension'] ?? 'jpg';
        $filename = 'imma-' . Str::slug($pathInfo['filename'] ?? Str::random(8)) . '.' . $extension;
        $storagePath = 'media/' . $filename;

        if (! Storage::disk(config('filesystems.default'))->put($storagePath, $content)) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);

        $file = File::create([
            'user_id' => 1,
            'disk' => config('filesystems.default'),
            'filename' => substr($filename, 0, 255),
            'path' => $storagePath,
            'extension' => $extension,
            'mime' => $mime ?: 'image/jpeg',
            'size' => strlen($content),
        ]);

        return $this->imageCache[$url] = $file->id;
    }


    private function syncEntityFiles(string $entityType, int $entityId, array $files): void
    {
        foreach ($files as $zone => $fileIds) {
            $requestedIds = array_values(array_filter(array_map('intval', array_wrap($fileIds))));

            DB::table('entity_files')
                ->where('entity_type', $entityType)
                ->where('entity_id', $entityId)
                ->where('zone', $zone)
                ->delete();

            if ($requestedIds === []) {
                continue;
            }

            $existingIds = File::whereIn('id', $requestedIds)->pluck('id')->all();

            foreach ($existingIds as $fileId) {
                DB::table('entity_files')->insert([
                    'file_id' => $fileId,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'zone' => $zone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }


    /**
     * @param array<int, string> $tags
     * @return array<int, int>
     */
    private function resolveCategoryIds(array $tags): array
    {
        $ids = [];

        foreach ($tags as $tag) {
            $key = Str::lower($tag);
            $slug = $this->categoryMap[$key] ?? null;

            if (! $slug) {
                continue;
            }

            $category = Category::withoutGlobalScope('active')->where('slug', $slug)->first();

            if ($category) {
                $ids[] = $category->id;
            }
        }

        if (empty($ids)) {
            $fallback = Category::withoutGlobalScope('active')->where('slug', 'spa')->first();

            if ($fallback) {
                $ids[] = $fallback->id;
            }
        }

        return array_values(array_unique($ids));
    }


    /**
     * @param array<int, string> $tags
     * @return array<int, int>
     */
    private function resolveTagIds(array $tags): array
    {
        $ids = [];

        foreach ($tags as $name) {
            $tag = Tag::whereHas('translations', function ($q) use ($name) {
                $q->where('name', $name);
            })->first();

            if (! $tag) {
                $tag = Tag::create(['name' => $name]);
            }

            $ids[] = $tag->id;
        }

        return array_values(array_unique($ids));
    }


    private function buildCategoryMap(): array
    {
        return [
            'spa' => 'spa',
            'facial' => 'facial',
            'massage' => 'massage',
            'estetik' => 'aesthetic-estetik',
            'aesthetic' => 'aesthetic-estetik',
            'botox' => 'botox',
            'filler' => 'filler',
            'benang' => 'benang',
            'drip' => 'drip',
            'combo drip' => 'drip',
            'laser' => 'laser',
            'pico laser' => 'laser',
            'cosmetik' => 'cosmetik',
            'bath & body' => 'body-care',
            'manicure' => 'manicure-pedicure',
            'pedicure' => 'manicure-pedicure',
            'eye lash' => 'eyelash-brow',
            'waxing' => 'waxing',
            'skin booster' => 'skin-booster',
            'whitening booster' => 'whitening-booster',
            'lipo' => 'lipo',
            'surgery' => 'surgery',
            'injection' => 'injection',
            'skin care' => 'skincare',
            'skincare' => 'skincare',
            'makeup' => 'makeup',
            'bridal package' => 'bridal-package',
            'promo' => 'aesthetic-estetik',
            'promo merdeka sale' => 'aesthetic-estetik',
            'promo year end t2' => 'aesthetic-estetik',
            'prp' => 'facial',
            'rejuran' => 'skin-booster',
            'juvelook' => 'skin-booster',
            'profilo' => 'skin-booster',
            'placentex' => 'injection',
            'pigment' => 'facial',
            'dimple' => 'dimple',
            'nosetip' => 'benang',
            'tipnose' => 'benang',
            'skinny shot' => 'lipo',
            'normal price' => 'spa',
        ];
    }


    private function fetch(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'AestheticCart-ImmaImporter/1.0',
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $html = @file_get_contents($url, false, $context);

        return $html !== false ? $html : null;
    }


    private function matchGroup(string $html, string $pattern): string
    {
        if (! preg_match($pattern, $html, $m)) {
            return '';
        }

        return trim($m[1]);
    }


    private function cleanHtml(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
        $html = preg_replace('#<script[^>]*>.*?</script>#is', '', $html);
        $html = preg_replace('#<style[^>]*>.*?</style>#is', '', $html);

        return trim($html);
    }


    /**
     * @return array{is_virtual: bool, manage_stock: bool, qty: int, in_stock: bool}
     */
    private function stockAttributes(): array
    {
        if ($this->physicalStock) {
            return [
                'is_virtual' => false,
                'manage_stock' => true,
                'qty' => $this->stockQty,
                'in_stock' => $this->stockQty > 0,
            ];
        }

        return [
            'is_virtual' => true,
            'manage_stock' => false,
            'qty' => 0,
            'in_stock' => true,
        ];
    }


    /**
     * @return array{manage_stock: bool, qty: int, in_stock: bool}
     */
    private function variantStockAttributes(): array
    {
        if ($this->physicalStock) {
            return [
                'manage_stock' => true,
                'qty' => $this->stockQty,
                'in_stock' => $this->stockQty > 0,
            ];
        }

        return [
            'manage_stock' => false,
            'qty' => 0,
            'in_stock' => true,
        ];
    }


    private function log(string $message): void
    {
        if ($this->logger) {
            ($this->logger)($message);
        }
    }
}
