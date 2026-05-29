<?php

namespace Modules\Category\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\Category\Entities\Category;

class PruneCategoriesToSpaAestheticSeeder extends Seeder
{
    private const KEEP_ROOT_SLUGS = ['spa', 'aesthetic-estetik', 'cosmetik'];


    /**
     * Remove all categories except SPA and AESTHETIC / ESTETIK trees.
     *
     * @return void
     */
    public function run(): void
    {
        $roots = Category::withoutGlobalScope('active')
            ->whereIn('slug', self::KEEP_ROOT_SLUGS)
            ->get();

        if ($roots->count() < 2) {
            $this->command?->error('SPA / Aesthetic / Cosmetik root categories not found. Run category seeders first.');

            return;
        }

        $keepIds = $roots->flatMap(fn (Category $root) => $this->collectDescendantIds($root))->unique()->values()->all();

        $deleted = Category::withoutGlobalScope('active')
            ->whereNotIn('id', $keepIds)
            ->delete();

        $this->resetStorefrontCategorySettings($roots);
        $this->resetMenuItems($keepIds);

        Cache::tags('categories')->flush();

        $remaining = Category::withoutGlobalScope('active')->count();

        $this->command?->info("Deleted {$deleted} categories. Remaining: {$remaining} (SPA + Aesthetic trees).");
    }


    /**
     * @return array<int, int>
     */
    private function collectDescendantIds(Category $category): array
    {
        $ids = [$category->id];

        $children = Category::withoutGlobalScope('active')
            ->where('parent_id', $category->id)
            ->get();

        foreach ($children as $child) {
            $ids = array_merge($ids, $this->collectDescendantIds($child));
        }

        return $ids;
    }


    /**
     * Point storefront sections at SPA and Aesthetic instead of removed demo categories.
     *
     * @param \Illuminate\Support\Collection<int, Category> $roots
     */
    private function resetStorefrontCategorySettings($roots): void
    {
        $spa = $roots->firstWhere('slug', 'spa');
        $aesthetic = $roots->firstWhere('slug', 'aesthetic-estetik');

        if (! $spa || ! $aesthetic) {
            return;
        }

        $categorySettingKeys = DB::table('settings')
            ->where('key', 'like', '%_category_id')
            ->pluck('key');

        $featured = [
            "storefront_featured_categories_section_category_1_category_id" => $spa->id,
            "storefront_featured_categories_section_category_2_category_id" => $aesthetic->id,
            "storefront_featured_categories_section_category_3_category_id" => $spa->id,
            "storefront_featured_categories_section_category_4_category_id" => $aesthetic->id,
            "storefront_featured_categories_section_category_5_category_id" => $spa->id,
            "storefront_featured_categories_section_category_6_category_id" => $aesthetic->id,
        ];

        foreach ($categorySettingKeys as $key) {
            $value = $featured[$key] ?? $spa->id;

            DB::table('settings')
                ->where('key', $key)
                ->update(['plain_value' => serialize((string) $value)]);
        }

        Cache::forget('settings');
    }


    /**
     * @param array<int, int> $keepIds
     */
    private function resetMenuItems(array $keepIds): void
    {
        DB::table('menu_items')
            ->whereNotNull('category_id')
            ->whereNotIn('category_id', $keepIds)
            ->update(['category_id' => null]);
    }
}
