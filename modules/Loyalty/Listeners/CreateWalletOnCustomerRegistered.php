<?php

namespace Modules\Loyalty\Listeners;

use Modules\User\Events\CustomerRegistered;
use Modules\Loyalty\Services\LoyaltyWalletService;

class CreateWalletOnCustomerRegistered
{
    public function __construct(private LoyaltyWalletService $wallets) {}


    public function handle(CustomerRegistered $event): void
    {
        $this->wallets->getOrCreateForUser($event->user);
    }
}
