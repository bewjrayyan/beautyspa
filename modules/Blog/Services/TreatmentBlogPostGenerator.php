<?php

namespace Modules\Blog\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Blog\Entities\BlogCategory;
use Modules\Blog\Entities\BlogPost;
use Modules\Blog\Entities\BlogTag;
use Modules\User\Entities\User;

class TreatmentBlogPostGenerator
{
    public function generate(bool $force = false): array
    {
        $created = 0;
        $skipped = 0;
        $refreshed = 0;

        foreach ($this->articles() as $article) {
            $existing = BlogPost::withoutGlobalScope('published')
                ->where('slug', $article['slug'])
                ->first();

            if ($existing && ! $force) {
                $skipped++;

                continue;
            }

            if ($existing) {
                $this->updatePost($existing, $article);
                $refreshed++;
            } else {
                $this->createPost($article);
                $created++;
            }
        }

        return compact('created', 'skipped', 'refreshed');
    }

    private function createPost(array $article): BlogPost
    {
        $userId = User::query()->value('id');
        $categoryId = BlogCategory::where('slug', $article['category_slug'])->value('id');

        $postId = DB::table('blog_posts')->insertGetId([
            'slug' => $article['slug'],
            'user_id' => $userId,
            'blog_category_id' => $categoryId,
            'publish_status' => BlogPost::PUBLISHED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->saveTranslations($postId, $article);
        $this->saveMeta($postId, $article);
        $this->syncTags($postId, $article['tags']);

        $post = BlogPost::withoutGlobalScope('published')->find($postId);
        app(BlogPostFeaturedImageService::class)->assignToPost($post);

        return $post->fresh(['tags', 'category', 'files']);
    }

    private function updatePost(BlogPost $post, array $article): void
    {
        $categoryId = BlogCategory::where('slug', $article['category_slug'])->value('id');

        DB::table('blog_posts')->where('id', $post->id)->update([
            'slug' => $article['slug'],
            'blog_category_id' => $categoryId,
            'publish_status' => BlogPost::PUBLISHED,
            'updated_at' => now(),
        ]);

        $this->saveTranslations($post->id, $article);
        $this->saveMeta($post->id, $article);
        $this->syncTags($post->id, $article['tags']);
        app(BlogPostFeaturedImageService::class)->assignToPost($post->fresh(['category', 'files']));
    }

    private function saveTranslations(int $postId, array $article): void
    {
        foreach (supported_locale_keys() as $locale) {
            DB::table('blog_post_translations')->updateOrInsert(
                ['blog_post_id' => $postId, 'locale' => $locale],
                [
                    'title' => $article['title'],
                    'description' => $article['description'],
                ]
            );
        }
    }

    private function saveMeta(int $postId, array $article): void
    {
        $post = BlogPost::withoutGlobalScope('published')->find($postId);
        $post->saveMetaData([
            'meta_title' => $article['title'] . ' | Imma Serilaris',
            'meta_description' => Str::limit(strip_tags($article['description']), 160, ''),
        ]);
    }

    private function syncTags(int $postId, array $tagNames): void
    {
        $tagIds = [];

        foreach ($tagNames as $name) {
            $tagIds[] = $this->ensureTag($name);
        }

        DB::table('blog_posts')->where('id', $postId)->update(['updated_at' => now()]);
        BlogPost::withoutGlobalScope('published')->find($postId)->tags()->sync($tagIds);
    }

    private function ensureTag(string $name): int
    {
        $slug = Str::slug($name) ?: Str::random(8);
        $existing = BlogTag::where('slug', $slug)->first();

        if ($existing) {
            return $existing->id;
        }

        $tagId = DB::table('blog_tags')->insertGetId([
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (supported_locale_keys() as $locale) {
            DB::table('blog_tag_translations')->updateOrInsert(
                ['blog_tag_id' => $tagId, 'locale' => $locale],
                ['name' => $name]
            );
        }

        return $tagId;
    }

    /**
     * @return array<int, array{title: string, slug: string, category_slug: string, tags: array<int, string>, description: string}>
     */
    private function articles(): array
    {
        return require __DIR__ . '/../Data/treatment_blog_articles.php';
    }
}
