<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Modules\Loyalty\Enums\TransactionType;
use Modules\Loyalty\Services\LoyaltyConfig;
use Modules\Loyalty\Services\LoyaltyOrderService;
use Modules\Loyalty\Services\LoyaltyWalletService;
use Modules\Order\Entities\Order;
use Modules\User\Entities\User;

class RepairLoyaltyRedemptionsCommand extends Command
{
    protected $signature = 'loyalty:repair-redemptions {--dry-run : Show what would be repaired without writing}';

    protected $description = 'Backfill missing loyalty redeem ledger rows and discount amounts for past orders.';


    public function handle(
        LoyaltyOrderService $orders,
        LoyaltyWalletService $wallets,
        LoyaltyConfig $config
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $repaired = 0;
        $discountFixed = 0;

        // Newest first so recent checkouts are corrected before older backlog.
        $orderIds = Order::query()
            ->where('loyalty_points_redeemed', '>', 0)
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($orderIds as $orderId) {
            $order = Order::query()->find($orderId);

            if (! $order) {
                continue;
            }

            $points = (int) $order->loyalty_points_redeemed;
            $discount = (float) ($order->getAttributes()['loyalty_discount_amount'] ?? 0);

            if ($discount <= 0 && $points > 0) {
                $discount = $config->pointsToRm($points);

                if (! $dryRun) {
                    $order->forceFill(['loyalty_discount_amount' => $discount])->saveQuietly();
                }

                $discountFixed++;
                $this->line("Order #{$order->id}: set discount RM {$discount}");
            }

            if (! $order->customer_id) {
                continue;
            }

            $user = User::find($order->customer_id);

            if (! $user) {
                continue;
            }

            $wallet = $wallets->getOrCreateForUser($user);
            $existing = $wallets->findExistingTransaction(
                $wallet,
                TransactionType::REDEEM,
                'order',
                $order->id . ':redeem'
            );

            if ($existing) {
                continue;
            }

            $balance = (int) $wallet->fresh()->balance;

            if ($balance < $points) {
                $this->error("Order #{$order->id}: skip debit — balance {$balance} < {$points} pts (needs manual adjust)");
                continue;
            }

            if ($dryRun) {
                $this->warn("Order #{$order->id}: would debit {$points} pts (balance {$balance})");
                $repaired++;
                continue;
            }

            $before = $balance;
            $orders->captureRedemptionFromCart($order->fresh());
            $after = (int) $wallet->fresh()->balance;
            $this->info("Order #{$order->id}: debited {$points} pts ({$before} → {$after})");
            $repaired++;
        }

        $this->info(($dryRun ? 'Dry-run' : 'Repaired') . ": {$repaired} redemptions, {$discountFixed} discount amounts.");

        return self::SUCCESS;
    }
}
