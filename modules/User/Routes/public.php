<?php

use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::get('login', 'AuthController@getLogin')->name('login');
Route::post('login', 'AuthController@postLogin')
    ->middleware('throttle:auth')
    ->name('login.post');

Route::get('login/{provider}', 'AuthController@redirectToProvider')->name('login.redirect');
Route::get('login/{provider}/callback', 'AuthController@handleProviderCallback')->name('login.callback');

Route::post('login/whatsapp/send-otp', 'WhatsAppOtpAuthController@sendOtp')
    ->middleware('throttle:6,1')
    ->name('login.whatsapp.send_otp');
Route::post('login/whatsapp/verify-otp', 'WhatsAppOtpAuthController@verifyOtp')
    ->middleware('throttle:10,1')
    ->name('login.whatsapp.verify_otp');

Route::get('logout', 'AuthController@getLogout')->name('logout');

Route::get('register', 'AuthController@getRegister')->name('register');
Route::post('register', 'AuthController@postRegister')
    ->name('register.post')
    ->middleware([ProtectAgainstSpam::class, 'throttle:forms']);

Route::get('password/reset', 'AuthController@getReset')->name('reset');
Route::post('password/reset', 'AuthController@postReset')
    ->middleware('throttle:auth')
    ->name('reset.post');
Route::get('password/reset/{email}/{code}', 'AuthController@getResetComplete')->name('reset.complete');
Route::post('password/reset/{email}/{code}', 'AuthController@postResetComplete')
    ->middleware('throttle:auth')
    ->name('reset.complete.post');
