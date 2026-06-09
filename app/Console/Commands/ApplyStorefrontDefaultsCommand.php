<?php

namespace AestheticCart\Console\Commands;

use AestheticCart\Install\StorefrontDefaults;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Modules\Category\Entities\Category;

class ApplyStorefrontDefaultsCommand extends Command
{
    protected $signature = 'storefront:apply-defaults
                            {--seed-categories : Also seed SPA/Aesthetic categories when none exist}';

    protected $description = 'Enable default storefront homepage sections for a fresh install';

    public function handle(StorefrontDefaults $defaults): int
    {
        if ($this->option('seed-categories') && Category::count() === 0) {
            $this->info('Seeding SPA/Aesthetic categories...');

            Artisan::call('db:seed', [
                '--class' => 'Modules\\Category\\Database\\Seeders\\SpaAestheticCategoriesSeeder',
                '--force' => true,
            ]);
        }

        $defaults->apply();

        $this->info('Storefront homepage defaults applied.');
        $this->line('Add products in Admin → Products, or run: php artisan imma:import-treatments');

        return self::SUCCESS;
    }
}
