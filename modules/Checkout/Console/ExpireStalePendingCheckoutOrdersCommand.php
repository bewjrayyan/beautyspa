<?php

namespace Modules\Checkout\Console;

use Illuminate\Console\Command;
use Modules\Checkout\Services\CheckoutCompletionGuard;
use Modules\Checkout\Services\OrderService;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Services\CheckoutSlotHoldService;

class ExpireStalePendingCheckoutOrdersCommand extends Command
{
    protected $signature = 'checkout:expire-stale-pending
                            {--hours=24 : Cancel unpaid online checkout orders older than this many hours}
                            {--dry-run : List matching orders without deleting them}';

    protected $description = 'Cancel abandoned online checkout orders (pending_payment) and purge expired slot holds';

    public function handle(OrderService $orderService, CheckoutSlotHoldService $holdService): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subHours($hours);

        $purgedHolds = $holdService->purgeExpired();
        $this->line("Purged {$purgedHolds} expired checkout slot hold(s).");

        $orders = Order::query()
            ->with('transaction')
            ->where('status', Order::PENDING_PAYMENT)
            ->where('payment_status', Order::PAYMENT_PENDING)
            ->where('created_at', '<', $cutoff)
            ->whereNotIn('payment_method', CheckoutCompletionGuard::offlineMethods())
            ->whereDoesntHave('transaction', fn ($query) => $query->whereNotNull('transaction_id'))
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No stale pending checkout orders found.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Found %d stale pending checkout order(s) older than %d hour(s).',
            $orders->count(),
            $hours
        ));

        $deleted = 0;

        foreach ($orders as $order) {
            $label = "#{$order->id} ({$order->payment_method}, {$order->created_at})";

            if ($dryRun) {
                $this->line("[dry-run] Would cancel {$label}");

                continue;
            }

            try {
                $orderService->delete($order);
                $deleted++;
                $this->line("Cancelled {$label}");
            } catch (\Throwable $exception) {
                report($exception);
                $this->error("Failed to cancel {$label}: {$exception->getMessage()}");
            }
        }

        if ($dryRun) {
            $this->warn('Dry run only — no orders were deleted.');
        } else {
            $this->info("Cancelled {$deleted} stale pending checkout order(s).");
        }

        return self::SUCCESS;
    }
}
