<?php

namespace Modules\Loyalty\Listeners;

use Modules\Checkout\Events\OrderPlaced;

/**
 * @deprecated Stamps are awarded on OrderStatusChanged (COMPLETED), same as points.
 * Kept as a no-op so any stale queue jobs / event caches fail closed safely.
 */
class AwardStampsOnOrderPlaced
{
    public function handle(OrderPlaced $event): void
    {
        // Intentionally empty — see ProcessLoyaltyOnOrderStatusChanged.
    }
}
