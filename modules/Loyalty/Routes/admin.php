<?php

use Illuminate\Support\Facades\Route;

Route::get('loyalty/tiers', [
    'as' => 'admin.loyalty.tiers.index',
    'uses' => 'TierController@index',
    'middleware' => 'can:admin.loyalty.tiers.index',
]);

Route::get('loyalty/tiers/create', [
    'as' => 'admin.loyalty.tiers.create',
    'uses' => 'TierController@create',
    'middleware' => 'can:admin.loyalty.tiers.create',
]);

Route::post('loyalty/tiers', [
    'as' => 'admin.loyalty.tiers.store',
    'uses' => 'TierController@store',
    'middleware' => 'can:admin.loyalty.tiers.create',
]);

Route::get('loyalty/tiers/{id}/edit', [
    'as' => 'admin.loyalty.tiers.edit',
    'uses' => 'TierController@edit',
    'middleware' => 'can:admin.loyalty.tiers.edit',
]);

Route::put('loyalty/tiers/{id}', [
    'as' => 'admin.loyalty.tiers.update',
    'uses' => 'TierController@update',
    'middleware' => 'can:admin.loyalty.tiers.edit',
]);

Route::delete('loyalty/tiers/{ids?}', [
    'as' => 'admin.loyalty.tiers.destroy',
    'uses' => 'TierController@destroy',
    'middleware' => 'can:admin.loyalty.tiers.destroy',
]);

Route::get('loyalty/tiers/index/table', [
    'as' => 'admin.loyalty.tiers.table',
    'uses' => 'TierController@table',
    'middleware' => 'can:admin.loyalty.tiers.index',
]);

Route::get('loyalty/reports', [
    'as' => 'admin.loyalty.reports.index',
    'uses' => 'LoyaltyReportController@index',
    'middleware' => 'can:admin.loyalty.reports.index',
]);

Route::get('loyalty/members', [
    'as' => 'admin.loyalty.members.index',
    'uses' => 'MemberController@index',
    'middleware' => 'can:admin.loyalty.members.index',
]);

Route::get('loyalty/members/index/table', [
    'as' => 'admin.loyalty.members.table',
    'uses' => 'MemberController@table',
    'middleware' => 'can:admin.loyalty.members.index',
]);

Route::get('loyalty/members/{wallet}', [
    'as' => 'admin.loyalty.members.show',
    'uses' => 'MemberController@show',
    'middleware' => 'can:admin.loyalty.members.show',
]);

Route::post('loyalty/members/{wallet}/adjust', [
    'as' => 'admin.loyalty.members.adjust',
    'uses' => 'MemberController@adjust',
    'middleware' => 'can:admin.loyalty.members.adjust',
]);
