<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Blog\Console\ImportImmaBlogPostCommand;
use Modules\Blog\Console\AssignBlogFeaturedImagesCommand;
use Modules\Blog\Console\GenerateTreatmentBlogPostsCommand;
use Modules\Blog\Console\SyncTreatmentBlogCategoriesCommand;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportImmaBlogPostCommand::class,
                SyncTreatmentBlogCategoriesCommand::class,
                GenerateTreatmentBlogPostsCommand::class,
                AssignBlogFeaturedImagesCommand::class,
            ]);
        }
    }
}
