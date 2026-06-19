<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Modules\Checkout\Services\OrderGoogleCalendarUrl;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Modules\Order\Services\SendOrderBeauticianNotification;
use Modules\Review\Entities\Review;

class AccountOrdersController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->with(['products', 'beautician', 'spaBranch'])
            ->latest()
            ->paginate(20);

        return view('storefront::public.account.orders.index', compact('orders'));
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show(int $id, OrderGoogleCalendarUrl $calendarUrl)
    {
        $order = $this->findUserOrder($id, [
            'products.product',
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
            'spaBranch',
        ]);

        $hasTreatmentBooking = $this->hasTreatmentBooking($order);
        $canNotifyBeautician = $hasTreatmentBooking
            && $order->beautician_id
            && setting('whatsapp_completed_beautician_enabled', true);
        $googleCalendarUrl = $calendarUrl->forOrder($order);
        $orderReviewItems = $this->orderReviewItems($order);
        $reviewerName = trim((auth()->user()->full_name ?: auth()->user()->email) ?? '');
        $orderRewards = $this->orderRewards($order);

        return view('storefront::public.account.orders.show', compact(
            'order',
            'hasTreatmentBooking',
            'canNotifyBeautician',
            'googleCalendarUrl',
            'orderReviewItems',
            'reviewerName',
            'orderRewards',
        ));
    }


    public function invoice(int $id)
    {
        $order = $this->findUserOrder($id, [
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
            'spaBranch',
        ]);

        $logo = null;
        $logoId = setting('storefront_header_logo');

        if ($logoId) {
            $logo = File::find($logoId)?->path;
        }

        return view('order::admin.orders.print.show', compact('order', 'logo'));
    }


    public function receipt(int $id)
    {
        $order = $this->findUserOrder($id, [
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
            'spaBranch',
        ]);

        $logo = null;
        $logoId = setting('storefront_header_logo');

        if ($logoId) {
            $logo = File::find($logoId)?->path;
        }

        return view('storefront::public.account.orders.receipt', [
            'order' => $order,
            'logo' => $logo,
            'orderRewards' => $this->orderRewards($order),
        ]);
    }


    public function notifyBeautician(int $id, SendOrderBeauticianNotification $notification): RedirectResponse
    {
        $order = $this->findUserOrder($id, ['beautician']);

        if (! $order->beautician_id) {
            return redirect()
                ->route('account.orders.show', $id)
                ->with('error', trans('storefront::order_complete.beautician_notify_no_beautician'));
        }

        try {
            $notification->send($order);

            return redirect()
                ->route('account.orders.show', $id)
                ->with('success', trans('storefront::order_complete.beautician_notify_sent'));
        } catch (Exception $e) {
            return redirect()
                ->route('account.orders.show', $id)
                ->with('error', $e->getMessage());
        }
    }


    private function findUserOrder(int $id, array $with = []): Order
    {
        return auth()->user()
            ->orders()
            ->with($with)
            ->where('id', $id)
            ->firstOrFail();
    }


    private function hasTreatmentBooking(Order $order): bool
    {
        if ($order->beautician_id || $order->appointment_date) {
            return true;
        }

        return $order->products->contains(fn ($line) => (bool) $line->product?->is_virtual);
    }


    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function orderReviewItems(Order $order)
    {
        if (! setting('reviews_enabled')) {
            return collect();
        }

        $productIds = $order->products->pluck('product_id')->unique()->filter()->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        $userReviews = Review::withoutGlobalScope('approved')
            ->where('reviewer_id', auth()->id())
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        return $order->products
            ->unique('product_id')
            ->map(function ($line) use ($userReviews) {
                $product = $line->product;

                if (! $product || $product->trashed()) {
                    return null;
                }

                $review = $userReviews->get($product->id);

                return [
                    'product_id' => $product->id,
                    'name' => $line->name,
                    'url' => $line->url(),
                    'image' => $product->base_image->path ?? '',
                    'review' => $review ? [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'rating_percent' => $review->rating_percent,
                        'comment' => $review->comment,
                        'is_approved' => $review->is_approved,
                        'status' => $review->status(),
                        'created_at_formatted' => $review->created_at_formatted,
                    ] : null,
                ];
            })
            ->filter()
            ->values();
    }


    private function orderRewards(Order $order): ?array
    {
        if (! app('modules')->isEnabled('Loyalty')) {
            return null;
        }

        return app(\Modules\Loyalty\Services\LoyaltyOrderCompleteRewardsService::class)
            ->forOrder($order);
    }
}
