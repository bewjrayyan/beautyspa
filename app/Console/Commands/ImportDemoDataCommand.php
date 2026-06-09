<?php

namespace AestheticCart\Console\Commands;

use AestheticCart\Install\DemoDataTables;
use AestheticCart\Install\ImmaSeriLarisAdminSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class ImportDemoDataCommand extends Command
{
    protected $signature = 'db:import-demo
                            {--path= : Directory containing demo-data.sql (default: storage/app/demo-export)}
                            {--skip-truncate : Do not truncate tables before import}
                            {--extract-media : Extract demo-media.zip into storage/app/public}';

    protected $description = 'Import demo database content exported by db:export-demo';

    public function handle(): int
    {
        $dir = $this->option('path') ?: storage_path('app/demo-export');
        $sqlPath = $dir . '/demo-data.sql';

        if (! File::exists($sqlPath)) {
            $this->error("demo-data.sql not found in {$dir}");
            $this->line('Upload demo-data.sql from local export, or run: php artisan db:export-demo');

            return self::FAILURE;
        }

        if (! $this->option('skip-truncate')) {
            $this->truncateContentTables();
        }

        $this->info('Importing demo-data.sql...');

        if (! $this->importSql($sqlPath)) {
            return self::FAILURE;
        }

        (new ImmaSeriLarisAdminSettings())->apply();

        $mediaZip = $dir . '/demo-media.zip';

        if ($this->option('extract-media') || File::exists($mediaZip)) {
            $this->extractMedia($mediaZip);
        }

        Artisan::call('translation:refresh-cache', ['--sync' => true]);
        Artisan::call('optimize:clear');

        $this->newLine();
        $this->info('Demo data import finished.');
        $this->table(['Item', 'Count'], [
            ['Products', (string) DB::table('products')->count()],
            ['Categories', (string) DB::table('categories')->count()],
            ['Files (media)', (string) DB::table('files')->count()],
            ['Settings', (string) DB::table('settings')->count()],
        ]);

        return self::SUCCESS;
    }


    private function truncateContentTables(): void
    {
        $this->warn('Truncating demo content tables...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (array_reverse(DemoDataTables::content()) as $table) {
            if (! $this->tableExists($table)) {
                continue;
            }

            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }


    private function importSql(string $sqlPath): bool
    {
        $database = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $mysql = $this->mysqlBinary();

        $command = array_merge([
            $mysql,
            '-h', $host,
            '-P', (string) $port,
            '-u', $username,
        ], $password !== '' && $password !== null ? ['-p' . $password] : [], [
            $database,
        ]);

        $process = Process::fromShellCommandline(
            implode(' ', array_map('escapeshellarg', $command)) . ' < ' . escapeshellarg($sqlPath)
        );
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Import failed: ' . trim($process->getErrorOutput() ?: $process->getOutput()));
            $this->line('You can also import demo-data.sql via phpMyAdmin.');

            return false;
        }

        return true;
    }


    private function extractMedia(string $zipPath): void
    {
        if (! File::exists($zipPath)) {
            $this->warn('demo-media.zip not found — skip media extract.');

            return;
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            $this->warn('Could not open demo-media.zip');

            return;
        }

        $target = storage_path('app/public');
        File::ensureDirectoryExists($target);

        $zip->extractTo($target);
        $zip->close();

        $this->info('Media extracted to storage/app/public/');
    }


    private function tableExists(string $table): bool
    {
        return collect(DB::select('SHOW TABLES'))
            ->contains(fn ($row) => array_values((array) $row)[0] === $table);
    }


    private function mysqlBinary(): string
    {
        $candidates = [
            '/Applications/XAMPP/xamppfiles/bin/mysql',
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            'mysql',
        ];

        foreach ($candidates as $path) {
            if ($path === 'mysql' || is_executable($path)) {
                return $path;
            }
        }

        return 'mysql';
    }
}
