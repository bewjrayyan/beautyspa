<?php

use Illuminate\Support\Facades\Route;

Route::get('settings', [
    'as' => 'admin.settings.edit',
    'uses' => 'SettingController@edit',
    'middleware' => 'can:admin.settings.edit',
]);

Route::put('settings', [
    'as' => 'admin.settings.update',
    'uses' => 'SettingController@update',
    'middleware' => 'can:admin.settings.edit',
]);

Route::get('settings/onesender-logs', [
    'as' => 'admin.onesender_logs.index',
    'uses' => 'OneSenderMessageLogController@index',
    'middleware' => 'can:admin.settings.edit',
]);

Route::delete('settings/onesender-logs/{log}', [
    'as' => 'admin.onesender_logs.destroy',
    'uses' => 'OneSenderMessageLogController@destroy',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/onesender-logs/delete-filtered', [
    'as' => 'admin.onesender_logs.destroy_filtered',
    'uses' => 'OneSenderMessageLogController@destroyFiltered',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/onesender-logs/delete-all', [
    'as' => 'admin.onesender_logs.destroy_all',
    'uses' => 'OneSenderMessageLogController@destroyAll',
    'middleware' => 'can:admin.settings.edit',
]);

Route::get('settings/onesender-queue', [
    'as' => 'admin.onesender_queue.index',
    'uses' => 'OneSenderOutboundQueueController@index',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/onesender-queue/{message}/cancel', [
    'as' => 'admin.onesender_queue.cancel',
    'uses' => 'OneSenderOutboundQueueController@cancel',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/onesender-queue/cancel-all', [
    'as' => 'admin.onesender_queue.cancel_all',
    'uses' => 'OneSenderOutboundQueueController@cancelAll',
    'middleware' => 'can:admin.settings.edit',
]);

Route::delete('settings/onesender-queue/{message}', [
    'as' => 'admin.onesender_queue.destroy',
    'uses' => 'OneSenderOutboundQueueController@destroy',
    'middleware' => 'can:admin.settings.edit',
]);
