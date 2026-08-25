<?php

namespace Modules\Loyalty\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Loyalty\Services\LoyaltyStampAwardService;

class AwardStampsOnOrderPlaced implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [15, 60, 180];

    public function __construct(private LoyaltyStampAwardService $stamps) {}

    public function handle(OrderPlaced $event): void
    {
        if (! app('modules')->isEnabled('Loyalty')) {
            return;
        }

        $this->stamps->awardForOrder($event->order);
    }
}
