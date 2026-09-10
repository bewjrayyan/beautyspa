<?php

use Illuminate\Support\Facades\Route;

Route::get('leads/reporting', [
    'as' => 'admin.leads.reporting',
    'uses' => 'CentralReportingController',
    'middleware' => ['can:admin.leads.index', 'throttle:60,1'],
]);

Route::get('leads/central/metrics', [
    'as' => 'admin.leads.central.metrics',
    'uses' => 'CentralDashboardController@metrics',
    'middleware' => ['can:admin.leads.index', 'throttle:30,1'],
]);

Route::get('leads/payments', [
    'as' => 'admin.leads.payments.index',
    'uses' => 'CentralPaymentController@index',
    'middleware' => ['can:admin.leads.index', 'throttle:60,1'],
]);

Route::get('leads/customers', [
    'as' => 'admin.leads.customers.index',
    'uses' => 'CentralCustomerController@index',
    'middleware' => ['can:admin.leads.index', 'throttle:60,1'],
]);

Route::get('leads/wallet', [
    'as' => 'admin.leads.wallet.index',
    'uses' => 'CentralWalletController@index',
    'middleware' => ['can:admin.leads.index', 'throttle:60,1'],
]);

Route::get('leads/checkin', [
    'as' => 'admin.leads.checkin.index',
    'uses' => 'CentralCheckinController@index',
    'middleware' => ['can:admin.leads.index', 'throttle:60,1'],
]);

Route::get('leads/clearance', [
    'as' => 'admin.leads.clearance.index',
    'uses' => 'CentralClearanceController@index',
    'middleware' => ['can:admin.leads.index', 'throttle:60,1'],
]);

Route::get('leads/central/{view?}', [
    'as' => 'admin.leads.central',
    'uses' => 'CentralDashboardController',
    'middleware' => 'can:admin.leads.index',
])->where('view', 'overview|leads|import|imports|followup|sales|payments|customers|wallet|checkin|clearance|beauticians|branches|audit');

Route::get('leads/workspace', [
    'as' => 'admin.leads.workspace.index',
    'uses' => 'LeadWorkspaceController@index',
    'middleware' => ['can:admin.leads.index', 'throttle:60,1'],
]);

Route::post('leads/workspace', [
    'as' => 'admin.leads.workspace.store',
    'uses' => 'LeadWorkspaceController@store',
    'middleware' => ['can:admin.leads.create', 'throttle:30,1'],
]);

Route::get('leads/workspace/{id}', [
    'as' => 'admin.leads.workspace.show',
    'uses' => 'LeadWorkspaceController@show',
    'middleware' => ['can:admin.leads.show', 'throttle:60,1'],
])->whereNumber('id');

Route::patch('leads/workspace/{id}/status', [
    'as' => 'admin.leads.workspace.status',
    'uses' => 'LeadWorkspaceController@updateStatus',
    'middleware' => ['can:admin.leads.edit', 'throttle:60,1'],
])->whereNumber('id');

Route::put('leads/workspace/{id}', [
    'as' => 'admin.leads.workspace.update',
    'uses' => 'LeadWorkspaceController@update',
    'middleware' => ['can:admin.leads.edit', 'throttle:30,1'],
])->whereNumber('id');

Route::delete('leads/workspace/{id}', [
    'as' => 'admin.leads.workspace.destroy',
    'uses' => 'LeadWorkspaceController@destroy',
    'middleware' => ['can:admin.leads.destroy', 'throttle:30,1'],
])->whereNumber('id');

Route::get('leads/follow-up', [
    'as' => 'admin.leads.followup.index',
    'uses' => 'LeadWorkspaceController@followUps',
    'middleware' => ['can:admin.leads.index', 'throttle:60,1'],
]);

Route::post('leads/workspace/{id}/follow-up', [
    'as' => 'admin.leads.workspace.followup',
    'uses' => 'LeadWorkspaceController@markFollowedUp',
    'middleware' => ['can:admin.leads.edit', 'throttle:60,1'],
])->whereNumber('id');

Route::get('leads/import', [
    'as' => 'admin.leads.import.index',
    'uses' => 'LeadImportController@index',
    'middleware' => ['can:admin.leads.index', 'throttle:60,1'],
]);

Route::post('leads/import/preview', [
    'as' => 'admin.leads.import.preview',
    'uses' => 'LeadImportController@preview',
    'middleware' => ['can:admin.leads.create', 'throttle:20,1'],
]);

Route::post('leads/import/confirm', [
    'as' => 'admin.leads.import.confirm',
    'uses' => 'LeadImportController@confirm',
    'middleware' => ['can:admin.leads.create', 'throttle:10,1'],
]);
