<?php

use Illuminate\Support\Facades\Route;

Route::get('reports', [
    'as' => 'admin.reports.index',
    'uses' => 'ReportController@index',
    'middleware' => 'can:admin.reports.index',
]);

Route::get('reports/export', [
    'as' => 'admin.reports.export',
    'uses' => 'ReportController@export',
    'middleware' => 'can:admin.reports.index',
]);

Route::get('reports/products/search', [
    'as' => 'admin.reports.products.search',
    'uses' => 'SalesReportProductController@index',
    'middleware' => 'can:admin.reports.index',
]);

Route::get('reports/products/options', [
    'as' => 'admin.reports.products.options',
    'uses' => 'SalesReportProductController@options',
    'middleware' => 'can:admin.reports.index',
]);
