<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Loyalty\Entities\LoyaltyStampEntry;
use Modules\Loyalty\Services\LoyaltyStampAwardService;
use Modules\Order\Entities\Order;

class RepairStampAwardsCommand extends Command
{
    protected $signature = 'loyalty:repair-stamp-awards {--dry-run : Show what would be awarded without writing}';

    protected $description = 'Backfill missing visit stamps for completed/paid orders that qualify for active stamp programs.';


    public function handle(LoyaltyStampAwardService $stamps): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $awarded = 0;
        $skipped = 0;

        $orderIds = Order::query()
            ->where(function ($query) {
                $query->where('status', Order::COMPLETED)
                    ->orWhere('payment_status', Order::PAYMENT_PAID);
            })
            ->whereNotNull('customer_id')
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($orderIds as $orderId) {
            $order = Order::query()->with(['products.product'])->find($orderId);

            if (! $order) {
                continue;
            }

            $before = LoyaltyStampEntry::query()->where('order_id', $order->id)->count();

            if ($dryRun) {
                DB::beginTransaction();
                try {
                    $stamps->awardForOrder($order);
                    $after = LoyaltyStampEntry::query()->where('order_id', $order->id)->count();
                } finally {
                    DB::rollBack();
                }
            } else {
                $stamps->awardForOrder($order);
                $after = LoyaltyStampEntry::query()->where('order_id', $order->id)->count();
            }

            if ($after > $before) {
                $awarded++;
                $this->line("Order #{$order->id}: +" . ($after - $before) . ' stamp entry');
            } else {
                $skipped++;
            }
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Awarded {$awarded} order(s); skipped {$skipped}.");

        return self::SUCCESS;
    }
}
