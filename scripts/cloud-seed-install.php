<?php

/**
 * Seed the BeautySpa (AestheticCart) application data after a fresh
 * `migrate:fresh`. This mirrors the steps that
 * AestheticCart\Http\Controllers\InstallController::install performs
 * (admin account, store settings, app settings, currency, post-install),
 * minus the database migration itself.
 *
 * It is intended to run via `php artisan tinker scripts/cloud-seed-install.php`
 * with APP_INSTALLED=true so the `setting` binding is registered.
 *
 * Only used by scripts/cloud-bootstrap.sh when the database has not been
 * installed yet; it is never run against an already-populated database.
 */

use Illuminate\Support\Facades\Artisan;
use AestheticCart\Install\App;
use AestheticCart\Install\Store;
use AestheticCart\Install\AdminAccount;
use AestheticCart\Install\PostInstall;

$request = [
    'app_url' => getenv('APP_URL') ?: 'http://localhost:8000',
    'admin_first_name' => 'Super',
    'admin_last_name' => 'Admin',
    'admin_email' => getenv('BEAUTYSPA_ADMIN_EMAIL') ?: 'admin@beautyspa.local',
    'admin_phone' => '+60123456789',
    'admin_password' => getenv('BEAUTYSPA_ADMIN_PASSWORD') ?: 'password123',
    'store_name' => 'BeautySpa',
    'store_email' => 'store@beautyspa.local',
    'store_phone' => '+60123456789',
];

Artisan::call('optimize:clear');

fwrite(STDOUT, "[seed] Admin account\n");
app(AdminAccount::class)->setup($request);

fwrite(STDOUT, "[seed] Store settings\n");
app(Store::class)->setup($request);

fwrite(STDOUT, "[seed] App settings\n");
app(App::class)->setup($request);

fwrite(STDOUT, "[seed] Post-install (storage link, translations, storefront defaults)\n");
app(PostInstall::class)->run();

$db = \Illuminate\Support\Facades\DB::connection('mysql');
fwrite(STDOUT, sprintf(
    "[seed] Done. users=%d roles=%d settings=%d currency_rates=%d\n",
    $db->table('users')->count(),
    $db->table('roles')->count(),
    $db->table('settings')->count(),
    $db->table('currency_rates')->count()
));
