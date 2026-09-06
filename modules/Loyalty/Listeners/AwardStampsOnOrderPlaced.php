<?php

namespace Modules\Loyalty\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

use Modules\Checkout\Events\OrderPlaced;

/**
 * @deprecated Stamps are awarded on OrderStatusChanged (COMPLETED), same as points.
 * Kept as a no-op so any stale queue jobs / event caches fail closed safely.
 */
class AwardStampsOnOrderPlaced implements ShouldQueueAfterCommit
{
    public function handle(OrderPlaced $event): void
    {
        // Intentionally empty — see ProcessLoyaltyOnOrderStatusChanged.
    }
}
