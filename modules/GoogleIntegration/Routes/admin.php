<?php

use Illuminate\Support\Facades\Route;

Route::post('settings/google-sheets/test-connection', [
    'as' => 'admin.settings.google_sheets.test_connection',
    'uses' => 'GoogleSheetsSettingsController@testConnection',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/google-sheets/sync-all', [
    'as' => 'admin.settings.google_sheets.sync_all',
    'uses' => 'GoogleSheetsSettingsController@syncAll',
    'middleware' => 'can:admin.settings.edit',
]);

Route::get('settings/google-sheets/sync-all/count', [
    'as' => 'admin.settings.google_sheets.sync_all_count',
    'uses' => 'GoogleSheetsSettingsController@syncAllCount',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/google-sheets/sync-all/chunk', [
    'as' => 'admin.settings.google_sheets.sync_all_chunk',
    'uses' => 'GoogleSheetsSettingsController@syncAllChunk',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('orders/{order}/google-sheets/sync', [
    'as' => 'admin.orders.google_sheets.sync',
    'uses' => 'OrderGoogleSheetsController@sync',
    'middleware' => 'can:admin.orders.edit',
]);
