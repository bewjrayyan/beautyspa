<?php

namespace Modules\Loyalty\Listeners;

use Modules\Checkout\Events\OrderPlaced;
use Modules\Loyalty\Services\LoyaltyOrderService;

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
