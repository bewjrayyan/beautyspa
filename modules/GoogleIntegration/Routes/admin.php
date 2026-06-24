<?php

use Illuminate\Support\Facades\Route;

Route::post('settings/google-sheets/test-connection', [
    'as' => 'admin.settings.google_sheets.test_connection',
    'uses' => 'GoogleSheetsSettingsController@testConnection',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('orders/{order}/google-sheets/sync', [
    'as' => 'admin.orders.google_sheets.sync',
    'uses' => 'OrderGoogleSheetsController@sync',
    'middleware' => 'can:admin.orders.edit',
]);
