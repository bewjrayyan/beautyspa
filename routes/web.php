<?php

use Illuminate\Support\Facades\Route;

Route::get('install', 'InstallController@installation')->name('install.show');
Route::post('install', 'InstallController@install')->name('install.do');

Route::get('license', 'LicenseController@create')->name('license.create');
Route::post('license', 'LicenseController@store')->name('license.store');

if (class_exists(\Modules\Setting\Http\Controllers\CatalogSyncController::class)) {
    Route::get('catalog-sync/bundle', [\Modules\Setting\Http\Controllers\CatalogSyncController::class, 'bundle'])
        ->middleware('web')
        ->name('catalog_sync.bundle');
}
