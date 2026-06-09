<?php

namespace AestheticCart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Modules\Setting\Services\CatalogSyncService;

class ImportDemoDataCommand extends Command
{
    protected $signature = 'db:import-demo
                            {--path= : Directory containing demo-data.sql (default: storage/app/demo-export)}
                            {--skip-truncate : Do not truncate tables before import}
                            {--bundle : Import catalog-bundle.zip instead of loose files}';

    protected $description = 'Import demo database content exported by db:export-demo';

    public function handle(CatalogSyncService $catalogSync): int
    {
        $dir = $this->option('path') ?: $catalogSync->exportDir();

        if ($this->option('path')) {
            config(['setting.catalog_sync.export_dir' => $dir]);
        }

        try {
            if ($this->option('bundle') || File::exists($dir . '/catalog-bundle.zip')) {
                $counts = $catalogSync->importBundle(
                    $dir . '/catalog-bundle.zip',
                    ! $this->option('skip-truncate')
                );
            } else {
                $counts = $catalogSync->import(
                    $dir,
                    ! $this->option('skip-truncate')
                );
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Demo data import finished.');
        $this->table(['Item', 'Count'], collect($counts)->map(fn ($v, $k) => [ucfirst($k), (string) $v])->values()->all());

        return self::SUCCESS;
    }
}
