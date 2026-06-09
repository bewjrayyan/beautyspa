<?php

namespace Modules\Setting\Services;

use AestheticCart\Install\DemoDataTables;
use AestheticCart\Install\ImmaSeriLarisAdminSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class CatalogSyncService
{
    public function exportDir(): string
    {
        return (string) config('setting.catalog_sync.export_dir', storage_path('app/demo-export'));
    }


    public function bundlePath(): string
    {
        return $this->exportDir() . '/' . config('setting.catalog_sync.bundle_filename', 'catalog-bundle.zip');
    }


    public function exportUrl(): ?string
    {
        $token = $this->token();

        if ($token === '') {
            return null;
        }

        return url('catalog-sync/bundle') . '?token=' . urlencode($token);
    }


    public function isTokenValid(?string $token): bool
    {
        $expected = $this->token();

        return $expected !== '' && is_string($token) && hash_equals($expected, $token);
    }


    /**
     * @return array{sql_path: string, media_zip: string, media_files: int, tables: int}
     */
    public function export(): array
    {
        $dir = $this->exportDir();
        File::ensureDirectoryExists($dir);

        $tables = $this->resolveTables();
        $sqlPath = $dir . '/demo-data.sql';

        if (! $this->exportSql($tables, $sqlPath)) {
            throw new RuntimeException(trans('setting::messages.catalog_sync_export_failed'));
        }

        $mediaZip = $dir . '/demo-media.zip';
        $mediaCount = $this->createMediaZip($mediaZip);

        return [
            'sql_path' => $sqlPath,
            'media_zip' => $mediaZip,
            'media_files' => $mediaCount,
            'tables' => count($tables),
        ];
    }


    /**
     * @return array{path: string, size: int}
     */
    public function createBundle(): array
    {
        $export = $this->export();
        $bundlePath = $this->bundlePath();

        if (File::exists($bundlePath)) {
            File::delete($bundlePath);
        }

        $zip = new ZipArchive();

        if ($zip->open($bundlePath, ZipArchive::CREATE) !== true) {
            throw new RuntimeException(trans('setting::messages.catalog_sync_bundle_failed'));
        }

        $zip->addFile($export['sql_path'], 'demo-data.sql');
        $zip->addFile($export['media_zip'], 'demo-media.zip');
        $zip->addFromString('manifest.json', json_encode([
            'exported_at' => now()->toIso8601String(),
            'tables' => $export['tables'],
            'media_files' => $export['media_files'],
            'app_url' => config('app.url'),
        ], JSON_PRETTY_PRINT));
        $zip->close();

        return [
            'path' => $bundlePath,
            'size' => (int) filesize($bundlePath),
        ];
    }


    /**
     * @return array{products: int, categories: int, files: int, settings: int}
     */
    public function import(?string $dir = null, bool $truncate = true): array
    {
        $dir = $dir ?: $this->exportDir();
        $sqlPath = $dir . '/demo-data.sql';

        if (! File::exists($sqlPath)) {
            throw new RuntimeException(trans('setting::messages.catalog_sync_sql_missing'));
        }

        if ($truncate) {
            $this->truncateContentTables();
        }

        $this->importSql($sqlPath);

        (new ImmaSeriLarisAdminSettings())->apply();

        $mediaZip = $dir . '/demo-media.zip';

        if (File::exists($mediaZip)) {
            $this->extractMedia($mediaZip);
        }

        Artisan::call('translation:refresh-cache', ['--sync' => true]);
        Artisan::call('optimize:clear');

        return $this->counts();
    }


    /**
     * @return array{products: int, categories: int, files: int, settings: int}
     */
    public function importBundle(string $zipPath, bool $truncate = true): array
    {
        if (! File::exists($zipPath)) {
            throw new RuntimeException(trans('setting::messages.catalog_sync_bundle_missing'));
        }

        $tempDir = storage_path('app/catalog-sync-import-' . uniqid());
        File::ensureDirectoryExists($tempDir);

        try {
            $zip = new ZipArchive();

            if ($zip->open($zipPath) !== true) {
                throw new RuntimeException(trans('setting::messages.catalog_sync_bundle_open_failed'));
            }

            $zip->extractTo($tempDir);
            $zip->close();

            return $this->import($tempDir, $truncate);
        } finally {
            File::deleteDirectory($tempDir);
        }
    }


    /**
     * @return array{products: int, categories: int, files: int, settings: int}
     */
    public function pullFromSource(?string $sourceUrl = null, ?string $token = null): array
    {
        $url = trim((string) ($sourceUrl ?: setting('catalog_sync_source_url') ?: config('setting.catalog_sync.default_source_url')));

        if ($url === '') {
            throw new RuntimeException(trans('setting::messages.catalog_sync_source_missing'));
        }

        $token = $token ?: $this->token();

        if ($token === '') {
            throw new RuntimeException(trans('setting::messages.catalog_sync_token_missing'));
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        $downloadUrl = $url . $separator . 'token=' . urlencode($token);

        $response = Http::timeout(300)->get($downloadUrl);

        if (! $response->successful()) {
            throw new RuntimeException(trans('setting::messages.catalog_sync_pull_failed', [
                'status' => $response->status(),
            ]));
        }

        File::ensureDirectoryExists($this->exportDir());

        $bundlePath = $this->bundlePath();
        File::put($bundlePath, $response->body());

        return $this->importBundle($bundlePath);
    }


    public function bundleExists(): bool
    {
        return File::exists($this->bundlePath());
    }


    /**
     * @return array{products: int, categories: int, files: int, settings: int}
     */
    private function counts(): array
    {
        return [
            'products' => (int) DB::table('products')->count(),
            'categories' => (int) DB::table('categories')->count(),
            'files' => (int) DB::table('files')->count(),
            'settings' => (int) DB::table('settings')->count(),
        ];
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
    private function exportSql(array $tables, string $sqlPath): bool
    {
        if ($this->canUseMysqldump()) {
            return $this->exportSqlViaCli($tables, $sqlPath);
        }

        return $this->exportSqlViaPhp($tables, $sqlPath);
    }


    /**
     * @param  list<string>  $tables
     */
    private function exportSqlViaCli(array $tables, string $sqlPath): bool
    {
        $database = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $command = array_merge([
            $this->mysqldumpBinary(),
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
            return false;
        }

        $header = "-- AestheticCart catalog sync export\n"
            . '-- Generated: ' . now()->toDateTimeString() . "\n"
            . "SET FOREIGN_KEY_CHECKS=0;\n\n";

        File::put($sqlPath, $header . $process->getOutput() . "\nSET FOREIGN_KEY_CHECKS=1;\n");

        return true;
    }


    /**
     * @param  list<string>  $tables
     */
    private function exportSqlViaPhp(array $tables, string $sqlPath): bool
    {
        $lines = [
            '-- AestheticCart catalog sync export (PHP fallback)',
            '-- Generated: ' . now()->toDateTimeString(),
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        foreach ($tables as $table) {
            $rows = DB::table($table)->get();

            foreach ($rows as $row) {
                $columns = array_keys((array) $row);
                $values = array_map(function ($value) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    if (is_bool($value)) {
                        return $value ? '1' : '0';
                    }

                    return DB::getPdo()->quote((string) $value);
                }, array_values((array) $row));

                $lines[] = 'INSERT INTO `' . $table . '` (`' . implode('`, `', $columns) . '`) VALUES (' . implode(', ', $values) . ');';
            }
        }

        $lines[] = '';
        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';

        File::put($sqlPath, implode("\n", $lines));

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
            return 0;
        }

        $count = 0;

        if (File::isDirectory($mediaRoot)) {
            foreach (File::allFiles($mediaRoot) as $file) {
                $zip->addFile($file->getPathname(), 'media/' . $file->getRelativePathname());
                $count++;
            }
        }

        $zip->close();

        return $count;
    }


    private function truncateContentTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (array_reverse(DemoDataTables::content()) as $table) {
            if (! $this->tableExists($table)) {
                continue;
            }

            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }


    private function importSql(string $sqlPath): void
    {
        if ($this->canUseMysqlCli()) {
            $this->importSqlViaCli($sqlPath);

            return;
        }

        $this->importSqlViaPhp($sqlPath);
    }


    private function importSqlViaCli(string $sqlPath): void
    {
        $database = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $command = array_merge([
            $this->mysqlBinary(),
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
            throw new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()) ?: trans('setting::messages.catalog_sync_import_failed'));
        }
    }


    private function importSqlViaPhp(string $sqlPath): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $handle = fopen($sqlPath, 'rb');

        if ($handle === false) {
            throw new RuntimeException(trans('setting::messages.catalog_sync_import_failed'));
        }

        $statement = '';

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $statement .= $line;

            if (str_ends_with(rtrim($line), ';')) {
                DB::unprepared($statement);
                $statement = '';
            }
        }

        if (trim($statement) !== '') {
            DB::unprepared($statement);
        }

        fclose($handle);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }


    private function extractMedia(string $zipPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException(trans('setting::messages.catalog_sync_media_failed'));
        }

        $target = storage_path('app/public');
        File::ensureDirectoryExists($target);
        $zip->extractTo($target);
        $zip->close();
    }


    private function tableExists(string $table): bool
    {
        return collect(DB::select('SHOW TABLES'))
            ->contains(fn ($row) => array_values((array) $row)[0] === $table);
    }


    private function token(): string
    {
        $token = config('setting.catalog_sync.token');

        return is_string($token) ? trim($token) : '';
    }


    private function canUseMysqlCli(): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }

        $disabled = ini_get('disable_functions');

        if (is_string($disabled) && str_contains($disabled, 'shell_exec')) {
            return false;
        }

        return is_executable($this->mysqlBinary()) || $this->mysqlBinary() === 'mysql';
    }


    private function canUseMysqldump(): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }

        $disabled = ini_get('disable_functions');

        if (is_string($disabled) && str_contains($disabled, 'shell_exec')) {
            return false;
        }

        return is_executable($this->mysqldumpBinary()) || $this->mysqldumpBinary() === 'mysqldump';
    }


    private function mysqldumpBinary(): string
    {
        foreach (['/Applications/XAMPP/xamppfiles/bin/mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump', 'mysqldump'] as $path) {
            if ($path === 'mysqldump' || is_executable($path)) {
                return $path;
            }
        }

        return 'mysqldump';
    }


    private function mysqlBinary(): string
    {
        foreach (['/Applications/XAMPP/xamppfiles/bin/mysql', '/usr/bin/mysql', '/usr/local/bin/mysql', 'mysql'] as $path) {
            if ($path === 'mysql' || is_executable($path)) {
                return $path;
            }
        }

        return 'mysql';
    }
}
