<?php

namespace Modules\Blog\Services;

use Illuminate\Support\Facades\DB;
use Modules\Blog\Entities\BlogCategory;
use Modules\Blog\Entities\BlogPost;
use Modules\Category\Entities\Category;

class TreatmentBlogCategorySync
{
    /**
     * Blog post slug => product category slug.
     *
     * @var array<string, string>
     */
    private array $postCategoryMap = [
        'manfaat-aura-diamond-ad' => 'drip',
        'manfaat-lumina-luxe' => 'drip',
        'manfaat-pure-booster-vitamin-c' => 'drip',
        'manfaat-snow-pearl-untuk-kulit' => 'whitening-booster',
        'kekerapan-pengambilan-drip-menjaga-kesihatan-dan-kecantikan-anda' => 'drip',
        'panduan-penjagaan-breastfiller-buttfiller-selepas-rawatan' => 'filler',
    ];

    public function sync(): array
    {
        $created = 0;
        $updated = 0;

        Category::query()
            ->with('translations')
            ->orderBy('parent_id')
            ->orderBy('position')
            ->get()
            ->each(function (Category $productCategory) use (&$created, &$updated) {
                $existed = BlogCategory::where('slug', $productCategory->slug)->exists();
                $this->upsertBlogCategory($productCategory);

                if ($existed) {
                    $updated++;
                } else {
                    $created++;
                }
            });

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => BlogCategory::count(),
        ];
    }

    public function assignPosts(): array
    {
        $assigned = 0;

        foreach ($this->postCategoryMap as $postSlug => $categorySlug) {
            $post = BlogPost::withoutGlobalScope('published')->where('slug', $postSlug)->first();
            $category = BlogCategory::where('slug', $categorySlug)->first();

            if (! $post || ! $category) {
                continue;
            }

            $post->update(['blog_category_id' => $category->id]);
            $assigned++;
        }

        return ['assigned' => $assigned];
    }

    public function cleanupOrphans(): int
    {
        $removed = 0;

        BlogCategory::query()
            ->whereDoesntHave('blogPosts')
            ->where(function ($query) {
                $query->where('slug', 'like', '-%')
                    ->orWhere('slug', '')
                    ->orWhereNull('slug');
            })
            ->get()
            ->each(function (BlogCategory $category) use (&$removed) {
                DB::table('blog_category_translations')->where('blog_category_id', $category->id)->delete();
                $category->delete();
                $removed++;
            });

        return $removed;
    }

    public function categorySlugForPost(string $postSlug): ?string
    {
        return $this->postCategoryMap[$postSlug] ?? null;
    }

    public function categoryIdForSlug(string $categorySlug): ?int
    {
        return BlogCategory::where('slug', $categorySlug)->value('id');
    }

    private function upsertBlogCategory(Category $productCategory): BlogCategory
    {
        $category = BlogCategory::where('slug', $productCategory->slug)->first();

        if (! $category) {
            $categoryId = DB::table('blog_categories')->insertGetId([
                'slug' => $productCategory->slug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $category = BlogCategory::find($categoryId);
        }

        foreach (supported_locale_keys() as $locale) {
            $translation = $productCategory->translate($locale);
            $name = $translation?->name ?: $productCategory->name;

            DB::table('blog_category_translations')->updateOrInsert(
                [
                    'blog_category_id' => $category->id,
                    'locale' => $locale,
                ],
                [
                    'name' => $name,
                ]
            );
        }

        return $category->fresh(['translations']);
    }
}
