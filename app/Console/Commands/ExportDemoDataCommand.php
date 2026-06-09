<?php

namespace AestheticCart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Modules\Setting\Services\CatalogSyncService;

class ExportDemoDataCommand extends Command
{
    protected $signature = 'db:export-demo
                            {--output= : Output directory (default: storage/app/demo-export)}';

    protected $description = 'Export demo database content and media files for upload to production';

    public function handle(CatalogSyncService $catalogSync): int
    {
        if ($this->option('output')) {
            config(['setting.catalog_sync.export_dir' => $this->option('output')]);
        }

        $this->info('Exporting catalog data...');

        try {
            $export = $catalogSync->export();
            $bundle = $catalogSync->createBundle();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Export complete.');
        $this->table(['File', 'Size'], [
            ['demo-data.sql', $this->formatBytes(filesize($export['sql_path']))],
            ['demo-media.zip', $this->formatBytes(filesize($export['media_zip'])) . " ({$export['media_files']} files)"],
            ['catalog-bundle.zip', $this->formatBytes($bundle['size'])],
        ]);
        $this->line('Output: ' . $catalogSync->exportDir());

        if ($url = $catalogSync->exportUrl()) {
            $this->line('Export URL: ' . $url);
        }

        return self::SUCCESS;
    }


    private function formatBytes(int|false $bytes): string
    {
        if ($bytes === false) {
            return '0 B';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
