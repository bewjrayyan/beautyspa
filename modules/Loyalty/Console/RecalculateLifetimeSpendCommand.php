<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Services\LoyaltyEarnService;
use Modules\Loyalty\Services\LoyaltyLifetimeSpendService;
use Modules\Loyalty\Services\LoyaltyTierService;
use Modules\Order\Entities\Order;

class RecalculateLifetimeSpendCommand extends Command
{
    protected $signature = 'loyalty:recalculate-lifetime-spend
                            {--user= : Recalculate a single user id}
                            {--earn-missing : Also award points for completed orders that never earned}';

    protected $description = 'Recalculate loyalty wallet lifetime spend from completed orders';


    public function handle(
        LoyaltyLifetimeSpendService $lifetimeSpend,
        LoyaltyEarnService $earn,
        LoyaltyTierService $tiers
    ): int {
        $query = LoyaltyWallet::query();

        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        $wallets = $query->get();
        $updated = 0;

        foreach ($wallets as $wallet) {
            $before = (float) $wallet->lifetime_spend;
            $after = $lifetimeSpend->recalculateWallet($wallet);
            $tiers->evaluate($wallet->fresh(), 'lifetime_spend_sync');
            $updated++;

            $this->line(sprintf(
                'Wallet #%d (user %d): %s → %s',
                $wallet->id,
                $wallet->user_id,
                number_format($before, 2),
                number_format($after, 2)
            ));
        }

        if ($this->option('earn-missing')) {
            $orders = Order::query()
                ->where('status', Order::COMPLETED)
                ->where('customer_id', '!=', null)
                ->where('loyalty_points_earned', '<=', 0);

            if ($this->option('user')) {
                $orders->where('customer_id', $this->option('user'));
            }

            foreach ($orders->get() as $order) {
                $earn->earnFromCompletedOrder($order->fresh());
                $this->line('Earn processed for order #' . $order->id);
            }
        }

        $this->info("Recalculated lifetime spend for {$updated} wallet(s).");

        return self::SUCCESS;
    }
}
