<?php

use Illuminate\Support\Facades\Route;

Route::get('beauticians', [
    'as' => 'admin.beauticians.index',
    'uses' => 'BeauticianController@index',
    'middleware' => 'can:admin.beauticians.index',
]);

Route::get('beauticians/create', [
    'as' => 'admin.beauticians.create',
    'uses' => 'BeauticianController@create',
    'middleware' => 'can:admin.beauticians.create',
]);

Route::post('beauticians', [
    'as' => 'admin.beauticians.store',
    'uses' => 'BeauticianController@store',
    'middleware' => 'can:admin.beauticians.create',
]);

Route::get('beauticians/{id}/edit', [
    'as' => 'admin.beauticians.edit',
    'uses' => 'BeauticianController@edit',
    'middleware' => 'can:admin.beauticians.edit',
]);

Route::put('beauticians/{id}', [
    'as' => 'admin.beauticians.update',
    'uses' => 'BeauticianController@update',
    'middleware' => 'can:admin.beauticians.edit',
]);

Route::post('beauticians/{id}/reset-portal-password', [
    'as' => 'admin.beauticians.reset_portal_password',
    'uses' => 'BeauticianController@resetPortalPassword',
    'middleware' => 'can:admin.beauticians.edit',
]);

Route::delete('beauticians/{ids?}', [
    'as' => 'admin.beauticians.destroy',
    'uses' => 'BeauticianController@destroy',
    'middleware' => 'can:admin.beauticians.destroy',
]);

Route::get('beauticians/index/table', [
    'as' => 'admin.beauticians.table',
    'uses' => 'BeauticianController@table',
    'middleware' => 'can:admin.beauticians.index',
]);

Route::get('beauticians/{id}/schedule/calendar', [
    'as' => 'admin.beauticians.schedule.calendar',
    'uses' => 'BeauticianScheduleController@calendarEvents',
    'middleware' => 'can:admin.beauticians.edit',
]);

Route::get('beauticians/{id}/schedule/kanban', [
    'as' => 'admin.beauticians.schedule.kanban',
    'uses' => 'BeauticianScheduleController@kanbanBoard',
    'middleware' => 'can:admin.beauticians.edit',
]);

Route::patch('beauticians/{id}/schedule/{booking}/status', [
    'as' => 'admin.beauticians.schedule.update_status',
    'uses' => 'BeauticianScheduleController@updateStatus',
    'middleware' => 'can:admin.beauticians.edit',
]);
