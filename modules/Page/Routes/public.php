<?php

use Illuminate\Support\Facades\Route;
use Modules\Support\Http\Middleware\CacheStaticResponse;

Route::get('/', 'HomeController@index')
    ->middleware(CacheStaticResponse::class)
    ->name('home');
