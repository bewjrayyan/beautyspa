<?php

use Illuminate\Support\Facades\Route;
use Modules\TreatmentReservation\Http\Controllers\Api\BookingController;

Route::middleware('throttle:30,1')->group(function () {
    Route::get('treatments', [BookingController::class, 'treatments']);
    Route::get('customers', [BookingController::class, 'customers']);
    Route::get('membership-lookup', [BookingController::class, 'membershipLookup']);
    Route::get('beauticians', [BookingController::class, 'beauticians']);
    Route::get('spa-branches', [BookingController::class, 'spaBranches']);

    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/', [BookingController::class, 'store']);
        Route::get('available-dates', [BookingController::class, 'availableDates']);
        Route::get('availability', [BookingController::class, 'availability']);
        Route::get('{booking}', [BookingController::class, 'show'])->whereNumber('booking');
        Route::put('{booking}', [BookingController::class, 'update'])->whereNumber('booking');
        Route::delete('{booking}', [BookingController::class, 'destroy'])->whereNumber('booking');
    });
});
