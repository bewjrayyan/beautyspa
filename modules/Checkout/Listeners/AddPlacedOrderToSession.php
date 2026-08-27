<?php

namespace Modules\Checkout\Listeners;

use Modules\Checkout\Events\OrderPlaced;
use Modules\Checkout\Services\CheckoutCompletionGuard;

/**
 * OrderPlaced listener — sets thank-you session.
 * User: checkout completes but thank-you page not shown.
 */
class AddPlacedOrderToSession
{
    /**
     * Handle the event.
     *
     * @param OrderPlaced $event
     *
     * @return void
     */
    public function handle($event)
    {
        CheckoutCompletionGuard::rememberPlacedOrder($event->order);
    }
}
