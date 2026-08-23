<?php

namespace Modules\Review\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Review\Entities\Review;
use Modules\Product\Entities\Product;
use Modules\Review\Http\Requests\StoreReviewRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductReviewController
{
    /**
     * Display a listing of the resource.
     *
     * @param int $productId
     *
     * @return Response
     */
    public function index($productId)
    {
        return Review::where('product_id', $productId)->latest()->paginate(5);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param int $productId
     * @param StoreReviewRequest $request
     *
     * @return Response
     */
    public function store($productId, StoreReviewRequest $request)
    {
        if (!setting('reviews_enabled')) {
            return;
        }

        $product = Product::findOrFail($productId);

        abort_unless($product->purchasedByUser(), 403);

        return DB::transaction(function () use ($product, $request) {
            Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            if (Review::withoutGlobalScope('approved')
                ->where('reviewer_id', auth()->id())
                ->where('product_id', $product->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'rating' => trans('review::messages.already_reviewed'),
                ]);
            }

            $review = $product->reviews()->create([
                'reviewer_id' => auth()->id(),
                'rating' => $request->rating,
                'reviewer_name' => $request->reviewer_name,
                'comment' => $request->comment,
                'is_approved' => setting('auto_approve_reviews', 0),
            ]);

            $rewardPoints = 0;

            if (app('modules')->isEnabled('Loyalty')) {
                $rewardPoints = app(\Modules\Loyalty\Services\LoyaltyReviewRewardService::class)
                    ->award(auth()->user(), $review);
            }

            return $review->setAttribute('reward_points', $rewardPoints);
        });
    }
}
