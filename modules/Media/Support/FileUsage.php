<?php

namespace Modules\Media\Support;

use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

class FileUsage
{
    /**
     * Setting keys that store a single file id (non _file_id suffix).
     *
     * @var list<string>
     */
    protected static array $directFileSettingKeys = [
        'storefront_favicon',
        'storefront_newsletter_bg_image',
        'storefront_accepted_payment_methods_image',
        'pwa_icon',
        'specialgift_voucher_background',
        'chip_checkout_logo',
        'chip_fpx_checkout_logo',
        'chip_card_checkout_logo',
        'chip_atome_checkout_logo',
        'chip_ewallet_checkout_logo',
        'chip_duitnow_checkout_logo',
    ];

    /**
     * Translatable settings that store a single file id.
     *
     * @var list<string>
     */
    protected static array $translatableFileSettingKeys = [
        'admin_logo',
        'admin_small_logo',
        'storefront_header_logo',
        'storefront_footer_logo',
        'storefront_mail_logo',
    ];


    /**
     * Files still used by live catalog, settings, or sliders.
     *
     * @return list<int>
     */
    public static function activelyUsedFileIds(): array
    {
        return collect()
            ->merge(static::fileIdsFromActiveEntityFiles())
            ->merge(static::fileIdsFromSettings())
            ->merge(static::fileIdsFromSliders())
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }


    /**
     * Media left over from soft-deleted products/variants (safe cleanup candidates).
     *
     * @return list<int>
     */
    public static function orphanedCleanupFileIds(): array
    {
        $activelyUsed = array_flip(static::activelyUsedFileIds());

        return collect(static::fileIdsFromDeletedCatalog())
            ->filter(fn ($id) => ! isset($activelyUsed[$id]))
            ->values()
            ->all();
    }


    /**
     * @return list<int>
     */
    public static function fileIdsFromActiveEntityFiles(): array
    {
        $activeProductIds = Product::query()->pluck('id');
        $activeVariantIds = ProductVariant::query()->pluck('id');

        if ($activeProductIds->isEmpty() && $activeVariantIds->isEmpty()) {
            return [];
        }

        return DB::table('entity_files')
            ->where(function ($query) use ($activeProductIds, $activeVariantIds) {
                if ($activeProductIds->isNotEmpty()) {
                    $query->where(function ($productQuery) use ($activeProductIds) {
                        $productQuery
                            ->where('entity_type', Product::class)
                            ->whereIn('entity_id', $activeProductIds);
                    });
                }

                if ($activeVariantIds->isNotEmpty()) {
                    $method = $activeProductIds->isEmpty() ? 'where' : 'orWhere';

                    $query->{$method}(function ($variantQuery) use ($activeVariantIds) {
                        $variantQuery
                            ->where('entity_type', ProductVariant::class)
                            ->whereIn('entity_id', $activeVariantIds);
                    });
                }
            })
            ->distinct()
            ->pluck('file_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }


    /**
     * @return list<int>
     */
    public static function fileIdsFromDeletedCatalog(): array
    {
        $trashedProductIds = Product::onlyTrashed()->pluck('id');
        $trashedVariantIds = ProductVariant::onlyTrashed()->pluck('id');

        if ($trashedProductIds->isEmpty() && $trashedVariantIds->isEmpty()) {
            return [];
        }

        return DB::table('entity_files')
            ->where(function ($query) use ($trashedProductIds, $trashedVariantIds) {
                if ($trashedProductIds->isNotEmpty()) {
                    $query->where(function ($productQuery) use ($trashedProductIds) {
                        $productQuery
                            ->where('entity_type', Product::class)
                            ->whereIn('entity_id', $trashedProductIds);
                    });
                }

                if ($trashedVariantIds->isNotEmpty()) {
                    $method = $trashedProductIds->isEmpty() ? 'where' : 'orWhere';

                    $query->{$method}(function ($variantQuery) use ($trashedVariantIds) {
                        $variantQuery
                            ->where('entity_type', ProductVariant::class)
                            ->whereIn('entity_id', $trashedVariantIds);
                    });
                }
            })
            ->distinct()
            ->pluck('file_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }


    /**
     * @return list<int>
     */
    public static function fileIdsFromSliders(): array
    {
        if (! DB::getSchemaBuilder()->hasTable('slider_slide_translations')) {
            return [];
        }

        return DB::table('slider_slide_translations')
            ->whereNotNull('file_id')
            ->pluck('file_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }


    /**
     * @return list<int>
     */
    public static function fileIdsFromSettings(): array
    {
        $ids = [];

        $settings = DB::table('settings')
            ->where(function ($query) {
                $query->where('key', 'like', '%\_file\_id')
                    ->orWhereIn('key', array_merge(
                        static::$directFileSettingKeys,
                        static::$translatableFileSettingKeys
                    ));
            })
            ->get(['id', 'key', 'is_translatable', 'plain_value']);

        foreach ($settings as $setting) {
            if ($setting->is_translatable) {
                $values = DB::table('setting_translations')
                    ->where('setting_id', $setting->id)
                    ->pluck('value');

                foreach ($values as $value) {
                    static::pushFileId($ids, $value);
                }

                continue;
            }

            static::pushFileId($ids, @unserialize($setting->plain_value));
        }

        return $ids;
    }


    /**
     * @param list<int> $ids
     */
    protected static function pushFileId(array &$ids, mixed $value): void
    {
        if (is_numeric($value) && (int) $value > 0) {
            $ids[] = (int) $value;
        }
    }


    /**
     * @param list<int> $fileIds
     *
     * @return array<int, true>
     */
    public static function activelyUsedLookup(array $fileIds): array
    {
        $fileIds = array_values(array_filter(array_map('intval', $fileIds)));

        if ($fileIds === []) {
            return [];
        }

        $usedIds = array_flip(static::activelyUsedFileIds());
        $lookup = [];

        foreach ($fileIds as $id) {
            if (isset($usedIds[$id])) {
                $lookup[$id] = true;
            }
        }

        return $lookup;
    }


    /**
     * @param list<int> $fileIds
     *
     * @return array<int, true>
     */
    public static function orphanedCleanupLookup(array $fileIds): array
    {
        $fileIds = array_values(array_filter(array_map('intval', $fileIds)));

        if ($fileIds === []) {
            return [];
        }

        $orphanIds = array_flip(static::orphanedCleanupFileIds());
        $lookup = [];

        foreach ($fileIds as $id) {
            if (isset($orphanIds[$id])) {
                $lookup[$id] = true;
            }
        }

        return $lookup;
    }
}
