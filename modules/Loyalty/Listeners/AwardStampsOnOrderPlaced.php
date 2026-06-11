<?php

namespace Modules\Loyalty\Listeners;

use Modules\Checkout\Events\OrderPlaced;
use Modules\Loyalty\Services\LoyaltyStampAwardService;

class AwardStampsOnOrderPlaced
{
    public function __construct(private LoyaltyStampAwardService $stamps) {}


    public function handle(OrderPlaced $event): void
    {
        if (! app('modules')->isEnabled('Loyalty')) {
            return;
        }

        $this->stamps->awardForOrder($event->order);
    }
}
