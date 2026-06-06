<?php

use Illuminate\Support\Facades\Route;

Route::get('spa-branches', [
    'as' => 'admin.spa_branches.index',
    'uses' => 'SpaBranchController@index',
    'middleware' => 'can:admin.spa_branches.index',
]);

Route::get('spa-branches/index/table', [
    'as' => 'admin.spa_branches.table',
    'uses' => 'SpaBranchController@table',
    'middleware' => 'can:admin.spa_branches.index',
]);

Route::get('spa-branches/create', [
    'as' => 'admin.spa_branches.create',
    'uses' => 'SpaBranchController@create',
    'middleware' => 'can:admin.spa_branches.create',
]);

Route::post('spa-branches', [
    'as' => 'admin.spa_branches.store',
    'uses' => 'SpaBranchController@store',
    'middleware' => 'can:admin.spa_branches.create',
]);

Route::get('spa-branches/{id}/edit', [
    'as' => 'admin.spa_branches.edit',
    'uses' => 'SpaBranchController@edit',
    'middleware' => 'can:admin.spa_branches.edit',
]);

Route::put('spa-branches/{id}', [
    'as' => 'admin.spa_branches.update',
    'uses' => 'SpaBranchController@update',
    'middleware' => 'can:admin.spa_branches.edit',
]);

Route::delete('spa-branches/{ids?}', [
    'as' => 'admin.spa_branches.destroy',
    'uses' => 'SpaBranchController@destroy',
    'middleware' => 'can:admin.spa_branches.destroy',
]);
