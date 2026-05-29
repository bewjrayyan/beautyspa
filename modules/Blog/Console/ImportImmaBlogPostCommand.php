<?php

namespace Modules\Blog\Console;

use Illuminate\Console\Command;
use Modules\Blog\Services\ImmaSeriLarisBlogImporter;

class ImportImmaBlogPostCommand extends Command
{
    protected $signature = 'blog:import-imma {url : Source blog post URL} {--force : Refresh if slug exists}';

    protected $description = 'Import a blog post from immaserilaris.com into AestheticCart';

    public function handle(ImmaSeriLarisBlogImporter $importer): int
    {
        $url = rtrim($this->argument('url'), '/') . '/';

        try {
            $post = $importer->import($url, (bool) $this->option('force'));
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Blog post imported: {$post->title} (ID {$post->id}, slug: {$post->slug})");
        $this->line('Category: ' . ($post->category?->name ?? '—'));
        $this->line('Tags: ' . $post->tags->pluck('name')->implode(', '));
        $this->line('Featured image: ' . ($post->featured_image->path ? 'yes' : 'no'));
        $this->line('Admin: ' . route('admin.blog_posts.edit', $post->id));
        $this->line('Storefront: ' . route('blog_posts.show', $post->slug));

        return self::SUCCESS;
    }
}
