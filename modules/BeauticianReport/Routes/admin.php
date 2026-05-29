<?php

use Illuminate\Support\Facades\Route;

Route::get('beautician-reports', [
    'as' => 'admin.beautician_reports.index',
    'uses' => 'BeauticianReportController@index',
    'middleware' => 'can:admin.beautician_reports.index',
]);

Route::get('beautician-reports/analytics/overview', [
    'as' => 'admin.beautician_reports.analytics.overview',
    'uses' => 'AnalyticsController@overview',
    'middleware' => 'can:admin.beautician_reports.index',
]);

Route::get('beautician-reports/analytics/sales-trend', [
    'as' => 'admin.beautician_reports.analytics.sales_trend',
    'uses' => 'AnalyticsController@salesTrend',
    'middleware' => 'can:admin.beautician_reports.index',
]);
