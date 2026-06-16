<?php

use Illuminate\Support\Facades\Route;

Route::get('checkout', 'CheckoutController@create')->name('checkout.create');
Route::post('checkout', 'CheckoutController@store')
    ->middleware('throttle:checkout')
    ->name('checkout.store');

Route::post('checkout/check-email', 'CheckoutAccountController@checkEmail')
    ->middleware('throttle:30,1')
    ->name('checkout.check_email');
Route::post('checkout/login', 'CheckoutAccountController@login')
    ->middleware('throttle:10,1')
    ->name('checkout.login');

Route::any('checkout/{orderId}/complete', 'CheckoutCompleteController@store')
    ->name('checkout.complete.store')
    ->withoutMiddleware(\AestheticCart\Http\Middleware\VerifyCsrfToken::class);
Route::get('checkout/complete', 'CheckoutCompleteController@show')->name('checkout.complete.show');
Route::get('checkout/complete/invoice', 'CheckoutCompleteController@invoice')->name('checkout.complete.invoice');
Route::post('checkout/complete/notify-beautician', 'CheckoutCompleteController@notifyBeautician')
    ->name('checkout.complete.notify_beautician');

Route::any('checkout/{orderId}/payment-canceled', 'PaymentCanceledController@store')
    ->middleware('throttle:checkout')
    ->name('checkout.payment_canceled.store')
    ->withoutMiddleware(\AestheticCart\Http\Middleware\VerifyCsrfToken::class);
