<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Modules\TreatmentReservation\Services\BookingSyncService;

class SyncTreatmentBookingsCommand extends Command
{
    protected $signature = 'treatment-reservation:sync-orders';

    protected $description = 'Sync treatment bookings from existing orders with beautician appointments';

    public function handle(BookingSyncService $sync): int
    {
        $trashed = $sync->trashBookingsWithoutActiveOrder();
        $removed = $sync->cleanupInvalidBookings();
        $count = $sync->syncAllOrders();

        if ($trashed > 0) {
            $this->warn("Trashed {$trashed} job(s) whose order was deleted.");
        }

        if ($removed > 0) {
            $this->warn("Removed {$removed} booking(s) without a virtual treatment product.");
        }

        $this->info("Synced {$count} treatment booking(s).");

        return self::SUCCESS;
    }
}
