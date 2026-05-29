<?php

namespace Modules\Product\Console\Commands;

use Illuminate\Console\Command;
use Modules\Product\Services\ImmaSeriLarisTreatmentImporter;

class ImportImmaTreatmentsCommand extends Command
{
    protected $signature = 'imma:import-treatments
                            {--limit=0 : Limit number of products to import}
                            {--skip-images : Skip downloading images}
                            {--url= : Import a single product URL only}
                            {--force : Re-import / refresh product when URL already exists}
                            {--physical : Import as physical product with stock (not virtual treatment)}
                            {--qty=100 : Initial stock quantity when --physical is used}
                            {--sync-variants : Re-import variants for existing products}
                            {--purge-duplicates : Remove duplicate products (same name or slug suffix)}';

    protected $description = 'Import treatments from immaserilaris.com (description from #tab-description) into AestheticCart products';

    public function handle(): int
    {
        $this->info('Importing treatments from immaserilaris.com...');

        $importer = new ImmaSeriLarisTreatmentImporter(function ($message) {
            $this->line($message);
        });

        if ($this->option('purge-duplicates')) {
            $stats = $importer->purgeAllDuplicateProducts();

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Removed total', $stats['removed']],
                    ['By duplicate name', $stats['by_name']],
                    ['By slug suffix', $stats['by_slug_suffix']],
                ]
            );

            $this->info('Done. Run php artisan sitemap:generate && php artisan cache:clear');

            return self::SUCCESS;
        }

        if ($this->option('sync-variants')) {
            $stats = $importer->syncVariantsForExisting(function ($message) {
                $this->line($message);
            });

            $this->table(['Metric', 'Count'], collect($stats)->map(fn ($v, $k) => [$k, $v])->values()->all());

            return self::SUCCESS;
        }

        if ($url = $this->option('url')) {
            $ok = $importer->importProductUrl(
                $url,
                (bool) $this->option('skip-images'),
                (bool) $this->option('force'),
                (bool) $this->option('physical'),
                max(0, (int) $this->option('qty'))
            );

            $this->info($ok ? 'Product imported or refreshed.' : 'Skipped or failed.');

            return self::SUCCESS;
        }

        $stats = $importer->importAll(
            (int) $this->option('limit'),
            (bool) $this->option('skip-images')
        );

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['URLs found', $stats['total']],
                ['Imported', $stats['imported']],
                ['Skipped (existing)', $stats['skipped']],
                ['Failed', $stats['failed']],
            ]
        );

        $this->info('Done. Run php artisan cache:clear if products do not appear on storefront.');

        return self::SUCCESS;
    }
}
