<?php

use Illuminate\Support\Facades\Route;

Route::get('treatment-reservations', [
    'as' => 'admin.treatment_reservations.index',
    'uses' => 'ReservationController@index',
    'middleware' => 'can:admin.treatment_reservations.index',
]);

Route::get('treatment-reservations/manual-bookings/slots', [
    'as' => 'admin.treatment_reservations.manual_bookings.slots',
    'uses' => 'ManualBookingController@slots',
    'middleware' => 'can:admin.treatment_reservations.index',
]);

Route::get('treatment-reservations/manual-bookings/customers', [
    'as' => 'admin.treatment_reservations.manual_bookings.customers',
    'uses' => 'ManualBookingController@customers',
    'middleware' => 'can:admin.treatment_reservations.index',
]);

Route::post('treatment-reservations/manual-bookings', [
    'as' => 'admin.treatment_reservations.manual_bookings.store',
    'uses' => 'ManualBookingController@store',
    'middleware' => 'can:admin.treatment_reservations.create',
]);

Route::put('treatment-reservations/manual-bookings/{booking}', [
    'as' => 'admin.treatment_reservations.manual_bookings.update',
    'uses' => 'ManualBookingController@update',
    'middleware' => 'can:admin.treatment_reservations.edit',
]);

Route::patch('treatment-reservations/manual-bookings/{booking}/cancel', [
    'as' => 'admin.treatment_reservations.manual_bookings.cancel',
    'uses' => 'ManualBookingController@cancel',
    'middleware' => 'can:admin.treatment_reservations.edit',
]);

Route::get('treatment-reservations/calendar/events', [
    'as' => 'admin.treatment_reservations.calendar',
    'uses' => 'ReservationController@calendarEvents',
    'middleware' => 'can:admin.treatment_reservations.index',
]);

Route::get('treatment-reservations/kanban/board', [
    'as' => 'admin.treatment_reservations.kanban',
    'uses' => 'ReservationController@kanbanBoard',
    'middleware' => 'can:admin.treatment_reservations.index',
]);

Route::get('treatment-reservations/export', [
    'as' => 'admin.treatment_reservations.export',
    'uses' => 'ReservationController@export',
    'middleware' => 'can:admin.treatment_reservations.index',
]);

Route::get('treatment-reservations/export/pdf', [
    'as' => 'admin.treatment_reservations.export_pdf',
    'uses' => 'ReservationController@exportPdf',
    'middleware' => 'can:admin.treatment_reservations.index',
]);

Route::patch('treatment-reservations/{id}/status', [
    'as' => 'admin.treatment_reservations.update_status',
    'uses' => 'ReservationController@updateStatus',
    'middleware' => 'can:admin.treatment_reservations.edit',
]);

Route::post('treatment-reservations/{id}/whatsapp', [
    'as' => 'admin.treatment_reservations.send_whatsapp',
    'uses' => 'ReservationController@sendCustomerWhatsApp',
    'middleware' => 'can:admin.treatment_reservations.edit',
]);

Route::get('treatment-reservations/crm/customers/profile', [
    'as' => 'admin.treatment_reservations.crm.customer_profile',
    'uses' => 'ReservationController@customerProfile',
    'middleware' => 'can:admin.treatment_reservations.index',
]);

Route::post('treatment-reservations/{id}/reminder', [
    'as' => 'admin.treatment_reservations.send_reminder',
    'uses' => 'ReservationController@sendCustomerReminder',
    'middleware' => 'can:admin.treatment_reservations.edit',
]);

Route::post('treatment-reservations/{id}/beautician-reminder', [
    'as' => 'admin.treatment_reservations.send_beautician_reminder',
    'uses' => 'ReservationController@sendBeauticianReminder',
    'middleware' => 'can:admin.treatment_reservations.edit',
]);

Route::patch('treatment-reservations/crm/specialists/{beautician}/availability', [
    'as' => 'admin.treatment_reservations.crm.specialist_availability',
    'uses' => 'ReservationController@toggleSpecialistAvailability',
    'middleware' => 'can:admin.treatment_reservations.edit',
]);

// Legacy / mistaken URL (route name suggests treatment-reservations/portal).
Route::get('treatment-reservations/portal', function () {
    return redirect()->route('admin.treatment_reservations.portal');
});

Route::middleware(['beautician.portal'])->group(function () {
    Route::get('my/job-sheet', [
        'as' => 'admin.treatment_reservations.portal',
        'uses' => 'PortalController@jobSheet',
    ]);

    Route::get('my/job-sheet/kanban', [
        'as' => 'admin.treatment_reservations.portal.kanban',
        'uses' => 'PortalController@kanbanBoard',
    ]);

    Route::get('my/job-sheet/calendar/events', [
        'as' => 'admin.treatment_reservations.portal.calendar',
        'uses' => 'PortalController@calendarEvents',
    ]);

    Route::patch('my/job-sheet/{id}/status', [
        'as' => 'admin.treatment_reservations.portal.update_status',
        'uses' => 'PortalController@updateStatus',
    ]);

    Route::patch('my/job-sheet/{id}/notes', [
        'as' => 'admin.treatment_reservations.portal.update_notes',
        'uses' => 'PortalController@updateBeauticianNotes',
    ]);

    Route::post('my/job-sheet/{id}/whatsapp', [
        'as' => 'admin.treatment_reservations.portal.send_whatsapp',
        'uses' => 'PortalController@sendCustomerWhatsApp',
    ]);

    Route::get('my/job-sheet/manual-bookings/slots', [
        'as' => 'admin.treatment_reservations.portal.manual_bookings.slots',
        'uses' => 'PortalManualBookingController@slots',
        'middleware' => 'can:admin.treatment_reservations.portal.create',
    ]);

    Route::get('my/job-sheet/manual-bookings/customers', [
        'as' => 'admin.treatment_reservations.portal.manual_bookings.customers',
        'uses' => 'PortalManualBookingController@customers',
        'middleware' => 'can:admin.treatment_reservations.portal.create',
    ]);

    Route::post('my/job-sheet/manual-bookings', [
        'as' => 'admin.treatment_reservations.portal.manual_bookings.store',
        'uses' => 'PortalManualBookingController@store',
        'middleware' => 'can:admin.treatment_reservations.portal.create',
    ]);

    Route::put('my/job-sheet/manual-bookings/{booking}', [
        'as' => 'admin.treatment_reservations.portal.manual_bookings.update',
        'uses' => 'PortalManualBookingController@update',
        'middleware' => 'can:admin.treatment_reservations.portal.create',
    ]);

    Route::patch('my/job-sheet/manual-bookings/{booking}/cancel', [
        'as' => 'admin.treatment_reservations.portal.manual_bookings.cancel',
        'uses' => 'PortalManualBookingController@cancel',
        'middleware' => 'can:admin.treatment_reservations.portal.create',
    ]);

    Route::get('my/account', [
        'as' => 'admin.treatment_reservations.portal.account',
        'uses' => 'PortalAccountController@edit',
    ]);

    Route::put('my/account/profile', [
        'as' => 'admin.treatment_reservations.portal.account.profile',
        'uses' => 'PortalAccountController@updateProfile',
    ]);

    Route::put('my/account/password', [
        'as' => 'admin.treatment_reservations.portal.account.password',
        'uses' => 'PortalAccountController@updatePassword',
    ]);

    Route::get('my/availability', [
        'as' => 'admin.treatment_reservations.portal.availability',
        'uses' => 'PortalAvailabilityController@edit',
    ]);

    Route::put('my/availability/hours', [
        'as' => 'admin.treatment_reservations.portal.availability.hours',
        'uses' => 'PortalAvailabilityController@updateHours',
    ]);

    Route::post('my/availability/blocks', [
        'as' => 'admin.treatment_reservations.portal.availability.blocks',
        'uses' => 'PortalAvailabilityController@storeBlock',
    ]);

    Route::delete('my/availability/blocks/{blockId}', [
        'as' => 'admin.treatment_reservations.portal.availability.blocks.destroy',
        'uses' => 'PortalAvailabilityController@destroyBlock',
    ]);
});
