<?php

namespace Modules\SpaBranch\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Order\Entities\Order;
use Modules\SpaBranch\Entities\SpaBranch;

class BackfillOrderSpaBranchCommand extends Command
{
    protected $signature = 'spa-branch:backfill-orders
                            {--dry-run : Preview changes without writing to the database}
                            {--default= : Default spa branch ID when inference fails}';

    protected $description = 'Backfill orders.spa_branch_id for existing orders missing a branch assignment';

    public function handle(): int
    {
        if (! Schema::hasColumn('orders', 'spa_branch_id')) {
            $this->error('Column orders.spa_branch_id does not exist. Run migrations first.');

            return self::FAILURE;
        }

        $defaultBranchId = $this->resolveDefaultBranchId();

        if ($defaultBranchId === null) {
            $this->error('No active spa branch found. Create a branch first or pass --default=ID.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $orders = Order::withTrashed()
            ->whereNull('spa_branch_id')
            ->get(['id', 'beautician_id']);

        if ($orders->isEmpty()) {
            $this->info('All orders already have spa_branch_id assigned.');

            return self::SUCCESS;
        }

        $beauticianBranchMap = DB::table('beautician_spa_branch')
            ->get(['beautician_id', 'spa_branch_id'])
            ->groupBy('beautician_id')
            ->map(fn ($rows) => $rows->pluck('spa_branch_id')->unique()->values());

        $updated = 0;
        $inferred = 0;
        $defaulted = 0;

        foreach ($orders as $order) {
            $branchId = null;

            if ($order->beautician_id && ($branchIds = $beauticianBranchMap->get($order->beautician_id)) && $branchIds->count() === 1) {
                $branchId = (int) $branchIds->first();
                $inferred++;
            } else {
                $branchId = $defaultBranchId;
                $defaulted++;
            }

            if (! $dryRun) {
                Order::withTrashed()
                    ->whereKey($order->id)
                    ->update(['spa_branch_id' => $branchId]);
            }

            $updated++;
        }

        $this->info(($dryRun ? '[dry-run] Would update' : 'Updated') . " {$updated} order(s).");
        $this->line("  Inferred from beautician: {$inferred}");
        $this->line("  Default branch (#{$defaultBranchId}): {$defaulted}");

        return self::SUCCESS;
    }

    private function resolveDefaultBranchId(): ?int
    {
        if ($this->option('default')) {
            $id = (int) $this->option('default');

            return SpaBranch::query()->whereKey($id)->where('is_active', true)->exists()
                ? $id
                : null;
        }

        return SpaBranch::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->value('id');
    }
}
