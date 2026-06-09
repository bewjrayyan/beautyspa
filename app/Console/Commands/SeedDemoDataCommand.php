<?php

namespace AestheticCart\Console\Commands;

use AestheticCart\Install\DemoDataRestorer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SeedDemoDataCommand extends Command
{
    protected $signature = 'db:seed-demo
                            {--skip-import : Skip importing products from immaserilaris.com}
                            {--reset-admin : Replace admin user with demo admin@beautyspa.local / 123456}';

    protected $description = 'Seed Imma Seri Laris demo content (categories, pages, menus, products) on production or local';

    public function handle(DemoDataRestorer $restorer): int
    {
        if ($this->option('reset-admin')) {
            if (! $this->confirm('This will replace the admin user. Continue?', false)) {
                return self::FAILURE;
            }

            $restorer->resetAdminUser();
            $this->info('Demo admin restored: admin@beautyspa.local / 123456');
        } else {
            $this->info('Keeping existing admin user.');
        }

        $restorer->seedContent(fn (string $message) => $this->line($message));

        if (! $this->option('skip-import')) {
            $restorer->importProducts(fn (string $message) => $this->line($message));
        }

        Artisan::call('optimize:clear');

        $this->newLine();
        $this->info('Demo data seed finished.');
        $this->table(['Item', 'Count'], [
            ['Products', (string) DB::table('products')->count()],
            ['Categories', (string) DB::table('categories')->count()],
            ['Pages', (string) DB::table('pages')->count()],
            ['Files (media)', (string) DB::table('files')->count()],
            ['Settings', (string) DB::table('settings')->count()],
        ]);

        $this->newLine();
        $this->line('Run on production after deploy: php artisan db:seed-demo');

        return self::SUCCESS;
    }
}
