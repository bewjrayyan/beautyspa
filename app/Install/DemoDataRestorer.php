<?php

namespace AestheticCart\Install;

use AestheticCart\Install\AdminPermissions;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Setting\Entities\Setting;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;

class DemoDataRestorer
{
    /**
     * @param  callable(string): void|null  $log
     */
    public function seedContent(?callable $log = null): void
    {
        $log ??= static fn (string $message) => null;

        $log('Applying Imma Seri Laris admin settings...');
        (new ImmaSeriLarisAdminSettings())->apply();

        $log('Running module seeders...');

        foreach ($this->seeders() as $seeder) {
            try {
                Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
                $log("  ✓ {$seeder}");
            } catch (\Throwable $e) {
                $log("  ✗ {$seeder}: {$e->getMessage()}");
            }
        }

        try {
            Artisan::call('db:seed', [
                '--class' => 'Modules\\Menu\\Database\\Seeders\\ImmaSeriLarisCategoryMenuSeeder',
                '--force' => true,
            ]);
            $log('  ✓ ImmaSeriLarisCategoryMenuSeeder');
        } catch (\Throwable $e) {
            $log("  ✗ ImmaSeriLarisCategoryMenuSeeder: {$e->getMessage()}");
        }

        foreach (['loyalty:grant-admin-permissions', 'spabranch:grant-admin-permissions'] as $command) {
            try {
                Artisan::call($command);
            } catch (\Throwable) {
                // Optional modules.
            }
        }

        (new StorefrontDefaults())->apply();

        Artisan::call('translation:refresh-cache', ['--sync' => true]);
    }


    /**
     * @param  callable(string): void|null  $log
     */
    public function importProducts(?callable $log = null): void
    {
        $log ??= static fn (string $message) => null;

        $log('Importing treatments from immaserilaris.com (this may take several minutes)...');
        Artisan::call('imma:import-treatments');
        $log(trim(Artisan::output()));
    }


    public function resetAdminUser(): User
    {
        foreach (['activations', 'role_translations', 'user_roles', 'users', 'roles'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        Artisan::call('db:seed', [
            '--class' => 'Modules\\User\\Database\\Seeders\\RolesTableSeeder',
            '--force' => true,
        ]);

        $adminRole = Role::whereTranslation('name', 'Admin')->first();

        if ($adminRole) {
            $adminRole->permissions = AdminPermissions::allGranted();
            $adminRole->save();
        }

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'BeautySpa',
            'email' => 'admin@beautyspa.local',
            'phone' => '0123456789',
            'password' => bcrypt('123456'),
        ]);

        $activation = Activation::create($admin);
        Activation::complete($admin, $activation->code);

        if ($adminRole) {
            $admin->roles()->sync([$adminRole->id]);
        }

        $customerRole = Role::whereTranslation('name', 'Customer')->first();

        if ($customerRole) {
            Setting::set('customer_role', $customerRole->id);
        }

        $adminLogo = setting('admin_logo');

        if ($adminLogo) {
            Setting::setMany([
                'translatable' => [
                    'storefront_header_logo' => $adminLogo,
                ],
            ]);
        }

        return $admin;
    }


    /**
     * @return list<string>
     */
    private function seeders(): array
    {
        return [
            'Modules\\Category\\Database\\Seeders\\SpaAestheticCategoriesSeeder',
            'Modules\\Category\\Database\\Seeders\\CosmetikCategorySeeder',
            'Modules\\TreatmentReservation\\Database\\Seeders\\TreatmentReservationDatabaseSeeder',
            'Modules\\Currency\\Database\\Seeders\\CurrencyDatabaseSeeder',
            'Modules\\Loyalty\\Database\\Seeders\\LoyaltyDatabaseSeeder',
            'Modules\\Menu\\Database\\Seeders\\MenuDatabaseSeeder',
            'Modules\\Option\\Database\\Seeders\\ImmaSeriLarisOptionsSeeder',
            'Modules\\Tag\\Database\\Seeders\\ImmaSeriLarisTagsSeeder',
            'Modules\\Page\\Database\\Seeders\\ImmaSeriLarisAboutPageSeeder',
            'Modules\\Page\\Database\\Seeders\\ImmaSeriLarisPrivacyPageSeeder',
            'Modules\\Page\\Database\\Seeders\\ImmaSeriLarisTermsPageSeeder',
            'Modules\\Page\\Database\\Seeders\\ImmaSeriLarisFaqPageSeeder',
            'Modules\\Translation\\Database\\Seeders\\ImmaSeriLarisLocalesSeeder',
            'Modules\\Translation\\Database\\Seeders\\ImmaSeriLarisMalayStorefrontTranslationsSeeder',
            'Modules\\Translation\\Database\\Seeders\\ImmaSeriLarisMalayContentTranslationsSeeder',
        ];
    }
}
