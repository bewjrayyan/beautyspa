<?php

namespace Modules\Loyalty\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Cart\Facades\Cart;
use Modules\Loyalty\Services\LoyaltyCartService;
use Modules\Loyalty\Exceptions\InvalidLoyaltyRedemptionException;

class CartLoyaltyController
{
    public function __construct(private LoyaltyCartService $loyaltyCart) {}


    public function quote(): JsonResponse
    {
        $this->loyaltyCart->refreshHoldIfExpired();

        $quote = $this->loyaltyCart->quoteForUser(
            auth()->user(),
            request()->filled('points') ? (int) request('points') : null
        );

        return response()->json($quote);
    }


    public function store(): \Modules\Cart\Cart
    {
        try {
            $this->loyaltyCart->apply(
                auth()->user(),
                request()->filled('points') ? (int) request('points') : null
            );
        } catch (InvalidLoyaltyRedemptionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return Cart::instance();
    }


    public function destroy(): \Modules\Cart\Cart
    {
        $this->loyaltyCart->remove();

        return Cart::instance();
    }
}
