<?php

namespace Modules\Blog\Console;

use Illuminate\Console\Command;
use Modules\Blog\Entities\BlogCategory;
use Modules\Blog\Services\TreatmentBlogCategorySync;

class SyncTreatmentBlogCategoriesCommand extends Command
{
    protected $signature = 'blog:sync-treatment-categories
                            {--assign-posts : Reassign imported blog posts to matching treatment categories}
                            {--cleanup : Remove empty duplicate Uncategorized categories}';

    protected $description = 'Create blog categories from product treatment categories and optionally reassign posts';

    public function handle(TreatmentBlogCategorySync $sync): int
    {
        $result = $sync->sync();

        $this->info("Blog categories synced from treatments: {$result['created']} created, {$result['updated']} updated ({$result['total']} total).");

        if ($this->option('assign-posts')) {
            $assigned = $sync->assignPosts();
            $this->info("Reassigned {$assigned['assigned']} blog post(s) to treatment categories.");
        }

        if ($this->option('cleanup')) {
            $removed = $sync->cleanupOrphans();
            $this->info("Removed {$removed} empty duplicate category(ies).");
        }

        $this->newLine();
        $this->table(
            ['ID', 'Slug', 'Name'],
            BlogCategory::query()
                ->orderBy('id')
                ->get()
                ->map(fn (BlogCategory $category) => [
                    $category->id,
                    $category->slug,
                    $category->name,
                ])
                ->all()
        );

        return self::SUCCESS;
    }
}
