<?php

use Illuminate\Support\Facades\Route;

Route::get('beautician/login', function () {
    return redirect()->route('admin.login', ['login' => 'whatsapp']);
})->name('beautician.login');

Route::get('my-appointments', [
    'as' => 'treatment_reservations.booking.lookup',
    'uses' => 'BookingSelfServiceController@index',
]);

Route::post('my-appointments/send-otp', [
    'as' => 'treatment_reservations.booking.send_otp',
    'uses' => 'BookingSelfServiceController@sendOtp',
])->middleware('throttle:6,1');

Route::post('my-appointments/verify-otp', [
    'as' => 'treatment_reservations.booking.verify_otp',
    'uses' => 'BookingSelfServiceController@verifyOtp',
])->middleware('throttle:10,1');

Route::post('my-appointments/logout', [
    'as' => 'treatment_reservations.booking.logout',
    'uses' => 'BookingSelfServiceController@logout',
]);

Route::patch('my-appointments/{id}/cancel', [
    'as' => 'treatment_reservations.booking.cancel',
    'uses' => 'BookingSelfServiceController@cancel',
]);

Route::patch('my-appointments/{id}/reschedule', [
    'as' => 'treatment_reservations.booking.reschedule',
    'uses' => 'BookingSelfServiceController@reschedule',
]);

Route::get('my-appointments/{id}/slots', [
    'as' => 'treatment_reservations.booking.slots',
    'uses' => 'BookingSelfServiceController@availableSlots',
]);

// Legacy /my-booking URLs (bookmarks, old links)
Route::get('my-booking', fn () => redirect()->route('treatment_reservations.booking.lookup', [], 301));

Route::post('my-booking/send-otp', 'BookingSelfServiceController@sendOtp')->middleware('throttle:6,1');
Route::post('my-booking/verify-otp', 'BookingSelfServiceController@verifyOtp')->middleware('throttle:10,1');
Route::post('my-booking/logout', 'BookingSelfServiceController@logout');
Route::patch('my-booking/{id}/cancel', 'BookingSelfServiceController@cancel');
Route::patch('my-booking/{id}/reschedule', 'BookingSelfServiceController@reschedule');
Route::get('my-booking/{id}/slots', 'BookingSelfServiceController@availableSlots');

Route::get('availability/beautician/{beautician}/slots', [
    'as' => 'treatment_reservations.availability.slots',
    'uses' => 'AvailabilitySlotsController',
]);

Route::get('calendar/beautician/{beautician}/{token}.ics', [
    'as' => 'treatment_reservations.calendar.feed',
    'uses' => 'CalendarFeedController',
]);
