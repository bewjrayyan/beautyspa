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

Route::middleware(['beautician.portal.access', 'beautician.portal.from_route'])->group(function () {
    Route::get('beauticians/{id}/portal', [
        'as' => 'admin.beauticians.portal',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@jobSheet',
    ]);

    Route::get('beauticians/{id}/portal/calendar', [
        'as' => 'admin.beauticians.portal.calendar_page',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@calendarPage',
    ]);

    Route::get('beauticians/{id}/portal/dashboard', [
        'as' => 'admin.beauticians.portal.dashboard',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@dashboard',
    ]);

    Route::get('beauticians/{id}/portal/customers/profile', [
        'as' => 'admin.beauticians.portal.customer_profile',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@customerProfile',
    ]);

    Route::patch('beauticians/{id}/portal/specialist-availability', [
        'as' => 'admin.beauticians.portal.specialist_availability',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@toggleOwnAvailability',
    ]);

    Route::post('beauticians/{id}/portal/{booking}/reminder', [
        'as' => 'admin.beauticians.portal.send_reminder',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalController@sendCustomerReminder',
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

    Route::get('beauticians/{id}/portal/availability', [
        'as' => 'admin.beauticians.portal.availability',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalAvailabilityController@edit',
    ]);

    Route::put('beauticians/{id}/portal/availability/hours', [
        'as' => 'admin.beauticians.portal.availability.hours',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalAvailabilityController@updateHours',
    ]);

    Route::post('beauticians/{id}/portal/availability/blocks', [
        'as' => 'admin.beauticians.portal.availability.blocks',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalAvailabilityController@storeBlock',
    ]);

    Route::delete('beauticians/{id}/portal/availability/blocks/{blockId}', [
        'as' => 'admin.beauticians.portal.availability.blocks.destroy',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalAvailabilityController@destroyBlock',
    ]);

    Route::get('beauticians/{id}/portal/account', [
        'as' => 'admin.beauticians.portal.account',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalAccountController@edit',
    ]);

    Route::put('beauticians/{id}/portal/account/profile', [
        'as' => 'admin.beauticians.portal.account.profile',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalAccountController@updateProfile',
    ]);

    Route::put('beauticians/{id}/portal/account/password', [
        'as' => 'admin.beauticians.portal.account.password',
        'uses' => '\Modules\TreatmentReservation\Http\Controllers\Admin\PortalAccountController@updatePassword',
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
