<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Foundation\Application;
use Modules\Order\Entities\Order;
use Modules\Review\Entities\Review;

class AccountReviewController
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        if (! setting('reviews_enabled')) {
            return view('storefront::public.account.reviews.index', [
                'pendingReviewItems' => collect(),
                'reviewRewardPoints' => 0,
            ]);
        }

        $reviewedProductIds = Review::withoutGlobalScope('approved')
            ->where('reviewer_id', auth()->id())
            ->pluck('product_id');

        $pendingReviewItems = $this->pendingReviewItems($reviewedProductIds);
        $reviewRewardPoints = 0;

        if (app('modules')->isEnabled('Loyalty')) {
            $reviewRewardPoints = app(\Modules\Loyalty\Services\LoyaltyConfig::class)
                ->reviewRewardPoints();
        }

        return view('storefront::public.account.reviews.index', compact(
            'pendingReviewItems',
            'reviewRewardPoints'
        ));
    }


    /**
     * @param Collection<int, int> $reviewedProductIds
     * @return Collection<int, array<string, mixed>>
     */
    private function pendingReviewItems(Collection $reviewedProductIds): Collection
    {
        return auth()->user()
            ->orders()
            ->where('status', Order::COMPLETED)
            ->with('products.product.files')
            ->latest()
            ->get()
            ->flatMap(function (Order $order) {
                return $order->products->map(function ($line) use ($order) {
                    $product = $line->product;

                    if (! $product || $product->trashed()) {
                        return null;
                    }

                    return [
                        'product_id' => $product->id,
                        'name' => $line->name,
                        'image' => $product->base_image->path ?? '',
                        'order_id' => $order->id,
                        'order_date' => $order->created_at?->toFormattedDateString(),
                        'review_url' => route('account.orders.show', $order->id) . '#reviews',
                    ];
                });
            })
            ->filter()
            ->reject(fn (array $item) => $reviewedProductIds->contains($item['product_id']))
            ->unique('product_id')
            ->values();
    }
}
