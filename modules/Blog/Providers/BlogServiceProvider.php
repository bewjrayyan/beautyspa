<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Blog\Console\ImportImmaBlogPostCommand;
use Modules\Blog\Console\AssignBlogFeaturedImagesCommand;
use Modules\Blog\Console\GenerateTreatmentBlogPostsCommand;
use Modules\Blog\Console\SyncTreatmentBlogCategoriesCommand;
use Modules\Blog\Entities\BlogPost;
use Modules\Page\Listeners\ClearPageResponseCache;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        BlogPost::saved(fn () => ClearPageResponseCache::flush());
        BlogPost::deleted(fn () => ClearPageResponseCache::flush());

        $this->commands([
            ImportImmaBlogPostCommand::class,
            SyncTreatmentBlogCategoriesCommand::class,
            GenerateTreatmentBlogPostsCommand::class,
            AssignBlogFeaturedImagesCommand::class,
        ]);
    }
}
