<?php

namespace AestheticCart\Console\Commands;

use AestheticCart\Install\DemoDataRestorer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class RestoreDatabaseCommand extends Command
{
    protected $signature = 'db:restore-dev
                            {--skip-import : Skip importing treatments from immaserilaris.com}
                            {--fresh : Run migrate:fresh before restoring (destructive)}';

    protected $description = 'Rebuild a wiped local database from seeders and immaserilaris.com imports';

    public function handle(DemoDataRestorer $restorer): int
    {
        if ($this->option('fresh')) {
            if (! $this->confirm('migrate:fresh will erase ALL tables. Continue?', false)) {
                return self::FAILURE;
            }

            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info(Artisan::output());
        }

        $this->info('Restoring roles and admin user...');
        $restorer->resetAdminUser();

        $restorer->seedContent(fn (string $message) => $this->line($message));

        if (! $this->option('skip-import')) {
            $restorer->importProducts(fn (string $message) => $this->line($message));
        }

        Artisan::call('optimize:clear');

        $this->newLine();
        $this->info('Database restore finished.');
        $this->table(['Item', 'Value'], [
            ['Admin login', 'admin@beautyspa.local'],
            ['Admin password', '123456'],
            ['Products', (string) DB::table('products')->count()],
            ['Categories', (string) DB::table('categories')->count()],
            ['Users', (string) DB::table('users')->count()],
        ]);

        return self::SUCCESS;
    }
}
