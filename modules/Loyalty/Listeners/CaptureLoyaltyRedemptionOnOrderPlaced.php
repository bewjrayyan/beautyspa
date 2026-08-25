<?php

namespace Modules\Loyalty\Listeners;

use Modules\Checkout\Events\OrderPlaced;
use Modules\Loyalty\Services\LoyaltyOrderService;

/**
 * Must run synchronously: captureRedemptionFromCart() reads the current Cart session.
 * Queuing would lose loyalty redemption after checkout clears the cart.
 */
class CaptureLoyaltyRedemptionOnOrderPlaced
{
    public function handle(OrderPlaced $event): void
    {
        if (! app('modules')->isEnabled('Loyalty')) {
            return;
        }

        app(LoyaltyOrderService::class)->captureRedemptionFromCart($event->order);
    }
}
