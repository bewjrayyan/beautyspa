<?php

namespace AestheticCart\Install;

use Illuminate\Support\Facades\Artisan;
use Modules\Setting\Entities\Setting;
use Modules\User\Entities\Role;
use Nwidart\Modules\Facades\Module;

class PostInstall
{
    public function run(): void
    {
        $this->createStorageLink();
        $this->syncTranslations();
        $this->syncAppVersion();
        $this->applyStorefrontDefaults();
        $this->grantModulePermissions();
    }

    private function createStorageLink(): void
    {
        if (is_link(public_path('storage'))) {
            return;
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable) {
            if (! is_dir(public_path('storage'))) {
                mkdir(public_path('storage'), 0755, true);
            }
        }
    }

    private function syncTranslations(): void
    {
        try {
            Artisan::call('translation:refresh-cache', ['--sync' => true]);
        } catch (\Throwable) {
            // Non-fatal during install.
        }
    }

    private function applyStorefrontDefaults(): void
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'Modules\\Category\\Database\\Seeders\\SpaAestheticCategoriesSeeder',
                '--force' => true,
            ]);
        } catch (\Throwable) {
            // Non-fatal during install.
        }

        try {
            (new StorefrontDefaults())->apply();
        } catch (\Throwable) {
            // Non-fatal during install.
        }
    }


    private function syncAppVersion(): void
    {
        $path = base_path('app/AestheticCart.php');

        if (! is_readable($path)) {
            return;
        }

        if (preg_match("/const VERSION = '([^']+)';/", file_get_contents($path), $matches)) {
            Setting::set('app_version', $matches[1]);
        }
    }

    private function grantModulePermissions(): void
    {
        $role = Role::find(1);

        if (! $role) {
            return;
        }

        $role->permissions = array_merge($role->permissions ?? [], AdminPermissions::allGranted());
        $role->save();
    }
}
