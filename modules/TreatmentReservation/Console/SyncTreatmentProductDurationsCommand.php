<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Modules\TreatmentReservation\Services\TreatmentProductDurationService;

class SyncTreatmentProductDurationsCommand extends Command
{
    protected $signature = 'treatment-reservation:sync-product-durations
                            {--dry-run : Preview assignments without saving}
                            {--force : Re-assign duration even when already set}';

    protected $description = 'Seed spa-duration attribute values and assign treatment durations to virtual products.';

    public function handle(TreatmentProductDurationService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $attribute = $service->ensureDurationAttribute();
        $this->info("Using duration attribute #{$attribute->id} ({$attribute->slug}).");

        if ($dryRun) {
            $this->warn('Dry run only — no database changes will be saved.');
        }

        $stats = $service->syncAllVirtualProducts($dryRun, $force);

        foreach ($stats['details'] as $line) {
            $this->line($line);
        }

        $this->newLine();
        $this->info("Synced: {$stats['synced']}");
        $this->info("Skipped (unchanged): {$stats['skipped']}");

        if ($dryRun) {
            $this->comment('Re-run without --dry-run to persist changes.');
        }

        return self::SUCCESS;
    }
}
