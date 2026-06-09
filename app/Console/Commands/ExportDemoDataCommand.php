<?php

namespace AestheticCart\Console\Commands;

use AestheticCart\Install\DemoDataTables;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class ExportDemoDataCommand extends Command
{
    protected $signature = 'db:export-demo
                            {--output= : Output directory (default: storage/app/demo-export)}';

    protected $description = 'Export demo database content and media files for upload to production';

    public function handle(): int
    {
        $outputDir = $this->option('output') ?: storage_path('app/demo-export');
        File::ensureDirectoryExists($outputDir);

        $tables = $this->resolveTables();
        $sqlPath = $outputDir . '/demo-data.sql';

        $this->info('Exporting ' . count($tables) . ' tables to demo-data.sql...');

        if (! $this->runMysqldump($tables, $sqlPath)) {
            return self::FAILURE;
        }

        $mediaZip = $outputDir . '/demo-media.zip';
        $this->info('Packaging media files...');
        $mediaCount = $this->createMediaZip($mediaZip);

        $readme = $outputDir . '/README.txt';
        File::put($readme, $this->readmeContents());

        $this->newLine();
        $this->info('Export complete.');
        $this->table(['File', 'Size'], [
            ['demo-data.sql', $this->formatBytes(filesize($sqlPath))],
            ['demo-media.zip', $this->formatBytes(filesize($mediaZip)) . " ({$mediaCount} files)"],
            ['README.txt', $this->formatBytes(filesize($readme))],
        ]);
        $this->newLine();
        $this->line("Output directory: {$outputDir}");

        return self::SUCCESS;
    }


    /**
     * @return list<string>
     */
    private function resolveTables(): array
    {
        $existing = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0]);

        return collect(DemoDataTables::content())
            ->intersect($existing)
            ->values()
            ->all();
    }


    /**
     * @param  list<string>  $tables
     */
    private function runMysqldump(array $tables, string $sqlPath): bool
    {
        $database = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $mysqldump = $this->mysqldumpBinary();

        $command = array_merge([
            $mysqldump,
            '--no-create-info',
            '--complete-insert',
            '--skip-triggers',
            '--single-transaction',
            '-h', $host,
            '-P', (string) $port,
            '-u', $username,
        ], $password !== '' && $password !== null ? ['-p' . $password] : [], [
            $database,
        ], $tables);

        $process = new Process($command);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('mysqldump failed: ' . trim($process->getErrorOutput() ?: $process->getOutput()));

            return false;
        }

        $header = "-- AestheticCart demo data export\n"
            . '-- Generated: ' . now()->toDateTimeString() . "\n"
            . "SET FOREIGN_KEY_CHECKS=0;\n\n";

        File::put($sqlPath, $header . $process->getOutput() . "\nSET FOREIGN_KEY_CHECKS=1;\n");

        return true;
    }


    private function createMediaZip(string $zipPath): int
    {
        $mediaRoot = storage_path('app/public/media');

        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            $this->error('Could not create demo-media.zip');

            return 0;
        }

        $count = 0;

        if (File::isDirectory($mediaRoot)) {
            foreach (File::allFiles($mediaRoot) as $file) {
                $relative = 'media/' . $file->getRelativePathname();
                $zip->addFile($file->getPathname(), $relative);
                $count++;
            }
        }

        $zip->close();

        return $count;
    }


    private function mysqldumpBinary(): string
    {
        $candidates = [
            '/Applications/XAMPP/xamppfiles/bin/mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'mysqldump',
        ];

        foreach ($candidates as $path) {
            if ($path === 'mysqldump' || is_executable($path)) {
                return $path;
            }
        }

        return 'mysqldump';
    }


    private function readmeContents(): string
    {
        return <<<'TXT'
Imma Seri Laris — demo data export
==================================

Upload to production server, then run:

1. Upload demo-data.sql and demo-media.zip to storage/app/demo-export/ on the server

2. Import database:
   php artisan db:import-demo

3. Or import via phpMyAdmin:
   - Open demo-data.sql
   - Before import, run: SET FOREIGN_KEY_CHECKS=0;
   - Import the SQL file

4. Extract demo-media.zip into storage/app/public/ on the server
   (files should land in storage/app/public/media/)

5. Clear cache:
   php artisan optimize:clear

Alternative (no SQL upload — rebuilds from seeders + live import):
   php artisan db:seed-demo

Keeps your existing admin login. Takes longer but downloads fresh product images.
TXT;
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
