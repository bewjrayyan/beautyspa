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

Route::get('beautician-job-titles', [
    'as' => 'admin.beautician_job_titles.index',
    'uses' => 'BeauticianJobTitleController@index',
    'middleware' => 'can:admin.beautician_job_titles.index',
]);

Route::get('beautician-job-titles/create', [
    'as' => 'admin.beautician_job_titles.create',
    'uses' => 'BeauticianJobTitleController@create',
    'middleware' => 'can:admin.beautician_job_titles.create',
]);

Route::post('beautician-job-titles', [
    'as' => 'admin.beautician_job_titles.store',
    'uses' => 'BeauticianJobTitleController@store',
    'middleware' => 'can:admin.beautician_job_titles.create',
]);

Route::get('beautician-job-titles/{id}/edit', [
    'as' => 'admin.beautician_job_titles.edit',
    'uses' => 'BeauticianJobTitleController@edit',
    'middleware' => 'can:admin.beautician_job_titles.edit',
]);

Route::put('beautician-job-titles/{id}', [
    'as' => 'admin.beautician_job_titles.update',
    'uses' => 'BeauticianJobTitleController@update',
    'middleware' => 'can:admin.beautician_job_titles.edit',
]);

Route::delete('beautician-job-titles/{ids?}', [
    'as' => 'admin.beautician_job_titles.destroy',
    'uses' => 'BeauticianJobTitleController@destroy',
    'middleware' => 'can:admin.beautician_job_titles.destroy',
]);

Route::get('beautician-job-titles/index/table', [
    'as' => 'admin.beautician_job_titles.table',
    'uses' => 'BeauticianJobTitleController@table',
    'middleware' => 'can:admin.beautician_job_titles.index',
]);

Route::middleware(['can:admin.beauticians.edit', 'beautician.portal.from_route'])->group(function () {
    Route::get('beauticians/{id}/portal', [
        'as' => 'admin.beauticians.portal',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@jobSheet',
    ]);

    Route::get('beauticians/{id}/portal/kanban', [
        'as' => 'admin.beauticians.portal.kanban',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@kanbanBoard',
    ]);

    Route::get('beauticians/{id}/portal/calendar/events', [
        'as' => 'admin.beauticians.portal.calendar',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@calendarEvents',
    ]);

    Route::patch('beauticians/{id}/portal/{booking}/status', [
        'as' => 'admin.beauticians.portal.update_status',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@updateStatus',
    ]);

    Route::patch('beauticians/{id}/portal/{booking}/notes', [
        'as' => 'admin.beauticians.portal.update_notes',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@updateBeauticianNotes',
    ]);

    Route::post('beauticians/{id}/portal/{booking}/whatsapp', [
        'as' => 'admin.beauticians.portal.send_whatsapp',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@sendCustomerWhatsApp',
    ]);
});

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
