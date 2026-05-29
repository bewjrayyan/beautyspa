<?php

use Illuminate\Support\Facades\Route;

Route::get('public', fn () => redirect()->route('admin.login', status: 301));
Route::get('public/{path}', fn () => redirect()->route('admin.login', status: 301))->where('path', '.*');

Route::get('locale/{locale}', 'LocaleController@switch')->name('admin.locale.switch');

Route::get('/', 'DashboardController@index')->name('admin.dashboard.index');

Route::get('/sales-analytics', [
    'as' => 'admin.sales_analytics.index',
    'uses' => 'SalesAnalyticsController@index',
    'middleware' => 'can:admin.orders.index',
]);
