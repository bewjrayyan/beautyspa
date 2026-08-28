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

Route::get('settings/operations', [
    'as' => 'admin.operations.index',
    'uses' => 'OperationsController@index',
    'middleware' => 'can:admin.operations.view',
]);

Route::delete('settings/operations/queue/{job}', [
    'as' => 'admin.operations.queue.cancel',
    'uses' => 'OperationsController@cancelPending',
    'middleware' => ['can:admin.operations.manage_queue', 'throttle:20,1'],
])->whereNumber('job');

Route::post('settings/operations/failed-jobs/{uuid}/retry', [
    'as' => 'admin.operations.failed.retry',
    'uses' => 'OperationsController@retryFailed',
    'middleware' => ['can:admin.operations.manage_queue', 'throttle:10,1'],
])->whereUuid('uuid');

Route::post('settings/operations/queue/process', [
    'as' => 'admin.operations.queue.process',
    'uses' => 'OperationsController@processQueue',
    'middleware' => ['can:admin.operations.manage_queue', 'throttle:5,1'],
]);

Route::post('settings/operations/consultations/{submission}/legal-hold', [
    'as' => 'admin.operations.legal_hold.place',
    'uses' => 'OperationsController@placeLegalHold',
    'middleware' => ['can:admin.operations.manage_retention', 'throttle:10,1'],
])->whereNumber('submission');

Route::delete('settings/operations/consultations/{submission}/legal-hold', [
    'as' => 'admin.operations.legal_hold.release',
    'uses' => 'OperationsController@releaseLegalHold',
    'middleware' => ['can:admin.operations.manage_retention', 'throttle:10,1'],
])->whereNumber('submission');

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

Route::post('settings/onesender-queue/process-due', [
    'as' => 'admin.onesender_queue.process_due',
    'uses' => 'OneSenderOutboundQueueController@processDue',
    'middleware' => 'can:admin.settings.edit',
]);

Route::delete('settings/onesender-queue/{message}', [
    'as' => 'admin.onesender_queue.destroy',
    'uses' => 'OneSenderOutboundQueueController@destroy',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/onesender-queue/delete-filtered', [
    'as' => 'admin.onesender_queue.destroy_filtered',
    'uses' => 'OneSenderOutboundQueueController@destroyFiltered',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/onesender-queue/delete-all', [
    'as' => 'admin.onesender_queue.destroy_all',
    'uses' => 'OneSenderOutboundQueueController@destroyAll',
    'middleware' => 'can:admin.settings.edit',
]);

Route::get('settings/catalog-sync/export', [
    'as' => 'admin.catalog_sync.export',
    'uses' => 'CatalogSyncController@export',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/catalog-sync/import', [
    'as' => 'admin.catalog_sync.import',
    'uses' => 'CatalogSyncController@import',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/catalog-sync/pull', [
    'as' => 'admin.catalog_sync.pull',
    'uses' => 'CatalogSyncController@pull',
    'middleware' => 'can:admin.settings.edit',
]);

Route::post('settings/catalog-sync/import-stored', [
    'as' => 'admin.catalog_sync.import_stored',
    'uses' => 'CatalogSyncController@importStored',
    'middleware' => 'can:admin.settings.edit',
]);
