<?php

use Illuminate\Support\Facades\Route;

Route::post('loyalty/stamp-redemptions/lookup', [
    'as' => 'admin.loyalty.stamp_redemptions.lookup',
    'uses' => 'StampRedemptionController@lookup',
    'middleware' => 'can:admin.loyalty.members.index',
]);

Route::post('loyalty/stamp-redemptions/{wallet}/fulfill', [
    'as' => 'admin.loyalty.stamp_redemptions.fulfill',
    'uses' => 'StampRedemptionController@fulfill',
    'middleware' => 'can:admin.loyalty.members.show',
]);

Route::get('loyalty/stamp-programs/products/search', [
    'as' => 'admin.loyalty.stamp_programs.products.search',
    'uses' => 'StampProgramProductController@search',
    'middleware' => 'can:admin.loyalty.stamp_programs.index',
]);

Route::get('loyalty/stamp-programs/categories/{category}/products', [
    'as' => 'admin.loyalty.stamp_programs.categories.products',
    'uses' => 'StampProgramProductController@categoryProducts',
    'middleware' => 'can:admin.loyalty.stamp_programs.index',
]);

Route::get('loyalty/stamp-programs/products/{product}', [
    'as' => 'admin.loyalty.stamp_programs.products.show',
    'uses' => 'StampProgramProductController@show',
    'middleware' => 'can:admin.loyalty.stamp_programs.index',
]);

Route::get('loyalty/stamp-programs', [
    'as' => 'admin.loyalty.stamp_programs.index',
    'uses' => 'StampProgramController@index',
    'middleware' => 'can:admin.loyalty.stamp_programs.index',
]);

Route::get('loyalty/stamp-programs/create', [
    'as' => 'admin.loyalty.stamp_programs.create',
    'uses' => 'StampProgramController@create',
    'middleware' => 'can:admin.loyalty.stamp_programs.create',
]);

Route::post('loyalty/stamp-programs', [
    'as' => 'admin.loyalty.stamp_programs.store',
    'uses' => 'StampProgramController@store',
    'middleware' => 'can:admin.loyalty.stamp_programs.create',
]);

Route::get('loyalty/stamp-programs/{id}/edit', [
    'as' => 'admin.loyalty.stamp_programs.edit',
    'uses' => 'StampProgramController@edit',
    'middleware' => 'can:admin.loyalty.stamp_programs.edit',
]);

Route::put('loyalty/stamp-programs/{id}', [
    'as' => 'admin.loyalty.stamp_programs.update',
    'uses' => 'StampProgramController@update',
    'middleware' => 'can:admin.loyalty.stamp_programs.edit',
]);

Route::delete('loyalty/stamp-programs/{ids?}', [
    'as' => 'admin.loyalty.stamp_programs.destroy',
    'uses' => 'StampProgramController@destroy',
    'middleware' => 'can:admin.loyalty.stamp_programs.destroy',
]);

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

Route::post('loyalty/members/enroll', [
    'as' => 'admin.loyalty.members.enroll',
    'uses' => 'MemberController@enrollMissing',
    'middleware' => 'can:admin.loyalty.members.enroll',
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
