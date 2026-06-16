<?php

use Illuminate\Support\Facades\Route;

Route::post('subscribers', 'SubscriberController@store')
    ->middleware('throttle:forms')
    ->name('subscribers.store');
