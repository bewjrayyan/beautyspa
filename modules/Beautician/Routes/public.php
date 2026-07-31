<?php

use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::get('beauticians/register', 'BeauticianRegistrationController@create')
    ->name('beauticians.register');

Route::post('beauticians/register', 'BeauticianRegistrationController@store')
    ->middleware([ProtectAgainstSpam::class, 'throttle:forms'])
    ->name('beauticians.register.store');

Route::get('beauticians/registration/pending', 'BeauticianRegistrationController@pending')
    ->name('beauticians.registration.pending');
