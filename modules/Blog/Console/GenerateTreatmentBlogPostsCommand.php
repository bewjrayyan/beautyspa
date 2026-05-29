<?php

namespace Modules\Blog\Console;

use Illuminate\Console\Command;
use Modules\Blog\Entities\BlogCategory;
use Modules\Blog\Entities\BlogPost;
use Modules\Blog\Entities\BlogTag;
use Modules\Blog\Services\TreatmentBlogPostGenerator;

class GenerateTreatmentBlogPostsCommand extends Command
{
    protected $signature = 'blog:generate-treatment-posts {--force : Overwrite existing posts with the same slug}';

    protected $description = 'Generate 20 educational blog posts for Imma Serilaris treatment categories';

    public function handle(TreatmentBlogPostGenerator $generator): int
    {
        $result = $generator->generate((bool) $this->option('force'));

        $this->info("Created: {$result['created']}, refreshed: {$result['refreshed']}, skipped: {$result['skipped']}");
        $this->newLine();
        $this->line('Total published posts: ' . BlogPost::withoutGlobalScope('published')->count());
        $this->line('Categories with posts: ' . BlogCategory::has('blogPosts')->count() . ' / ' . BlogCategory::count());
        $this->line('Tags in use: ' . BlogTag::has('blogPosts')->count() . ' / ' . BlogTag::count());

        return self::SUCCESS;
    }
}
