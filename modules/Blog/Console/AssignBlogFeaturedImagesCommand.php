<?php

namespace Modules\Blog\Console;

use Illuminate\Console\Command;
use Modules\Blog\Services\BlogPostFeaturedImageService;

class AssignBlogFeaturedImagesCommand extends Command
{
    protected $signature = 'blog:assign-featured-images {--force : Replace existing featured images}';

    protected $description = 'Assign featured images to blog posts from related treatment product images';

    public function handle(BlogPostFeaturedImageService $service): int
    {
        $result = $service->assignAll((bool) $this->option('force'));

        $this->info("Assigned: {$result['assigned']}, skipped (already set): {$result['skipped']}, failed: {$result['failed']}");

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
