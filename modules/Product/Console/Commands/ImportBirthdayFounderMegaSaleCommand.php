<?php

namespace Modules\Product\Console\Commands;

use Illuminate\Console\Command;
use Modules\Product\Services\ImmaSeriLarisTreatmentImporter;

class ImportBirthdayFounderMegaSaleCommand extends Command
{
    private const CAMPAIGN_SUFFIX = ' (Birthday Founder Mega Sale 2026)';

    protected $signature = 'imma:import-birthday-founder-mega-sale
                            {--slug= : Import a single product slug only}
                            {--skip-images : Skip uploading promo flyer images}
                            {--force : Re-import / refresh when product already exists}';

    protected $description = 'Import Birthday Founder Mega Sale 2026 treatment products from flyer catalog';

    public function handle(): int
    {
        $catalog = require module_path('Product', 'Data/birthday_founder_mega_sale_2026.php');
        $slugFilter = $this->option('slug');

        if ($slugFilter) {
            $catalog = array_values(array_filter(
                $catalog,
                fn (array $entry) => $entry['slug'] === $slugFilter
            ));

            if ($catalog === []) {
                $this->error('No catalog entry found for slug: ' . $slugFilter);

                return self::FAILURE;
            }
        }

        $importer = new ImmaSeriLarisTreatmentImporter(function ($message) {
            $this->line($message);
        });

        $canonicalSlugs = array_column($catalog, 'slug');
        $removed = $importer->removeBirthdayMegaSaleOrphans($canonicalSlugs);

        if ($removed > 0) {
            $this->warn("Removed {$removed} orphan/duplicate birthday mega sale product(s).");
        }

        $stats = ['total' => count($catalog), 'imported' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($catalog as $index => $entry) {
            $entry['name'] = $entry['name'] . self::CAMPAIGN_SUFFIX;

            $this->info(sprintf(
                '[%d/%d] %s (%d options)',
                $index + 1,
                $stats['total'],
                $entry['name'],
                count($entry['variations'] ?? [])
            ));

            try {
                $ok = $importer->importFromData(
                    $entry,
                    (bool) $this->option('skip-images'),
                    (bool) $this->option('force')
                );

                if ($ok) {
                    $stats['imported']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Throwable $e) {
                $stats['failed']++;
                $this->error('  ERROR: ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Products in catalog', $stats['total']],
                ['Imported / refreshed', $stats['imported']],
                ['Skipped (existing)', $stats['skipped']],
                ['Failed', $stats['failed']],
            ]
        );

        $this->info('Done. Run php artisan cache:clear if products do not appear on storefront.');

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
