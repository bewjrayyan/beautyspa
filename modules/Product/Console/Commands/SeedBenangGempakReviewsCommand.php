<?php

namespace Modules\Product\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Product\Entities\Product;
use Modules\Product\Services\RandomProductReviewGenerator;
use Modules\Review\Entities\Review;

class SeedBenangGempakReviewsCommand extends Command
{
    protected $signature = 'product:seed-random-reviews
                            {--slug= : Product slug (required if not using default)}
                            {--count=10 : Number of reviews to create}
                            {--fresh : Remove existing reviews for this product first}';

    protected $description = 'Seed random unique approved reviews for a treatment product';

    protected $aliases = ['product:seed-benang-gempak-reviews'];

    public function handle(RandomProductReviewGenerator $generator): int
    {
        $slug = $this->option('slug');

        if (! $slug) {
            $this->error('Please pass --slug=your-product-slug');

            return self::FAILURE;
        }

        $product = Product::withoutGlobalScope('active')
            ->withTrashed()
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            $this->error('Product not found: ' . $slug);

            return self::FAILURE;
        }

        if ($product->trashed()) {
            $product->restore();
        }

        if ($this->option('fresh')) {
            $removed = Review::withoutGlobalScope('approved')
                ->where('product_id', $product->id)
                ->delete();

            $this->line("Removed {$removed} existing review(s).");
        }

        $existing = Review::withoutGlobalScope('approved')
            ->where('product_id', $product->id)
            ->count();

        if ($existing > 0 && ! $this->option('fresh')) {
            $this->warn("Product already has {$existing} review(s). Use --fresh to replace.");

            return self::SUCCESS;
        }

        $count = max(1, min(50, (int) $this->option('count')));
        $reviews = $generator->generate($product, $count);

        foreach ($reviews as $review) {
            $createdAt = Carbon::now()
                ->subDays(random_int(3, 540))
                ->subHours(random_int(0, 23));

            Review::withoutGlobalScope('approved')->create([
                'product_id' => $product->id,
                'reviewer_id' => null,
                'rating' => $review['rating'],
                'reviewer_name' => $review['name'],
                'comment' => $review['comment'],
                'is_approved' => true,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $stats = Review::withoutGlobalScope('approved')
            ->selectRaw('count(*) as count, round(avg(rating), 1) as avg_rating')
            ->where('product_id', $product->id)
            ->first();

        $this->info("Added {$count} random reviews to: {$product->name}");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Product ID', $product->id],
                ['Total reviews', $stats->count],
                ['Average rating', $stats->avg_rating],
            ]
        );

        $this->line('Run: php artisan cache:clear');

        return self::SUCCESS;
    }
}
