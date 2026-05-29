<?php

use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

/*
| Locale-free public URL: /send-gift (not /{locale}/send-gift).
| Matches docs and avoids localization_redirect rewriting the link.
*/

Route::get('send-gift', [
    'as' => 'specialgift.send.create',
    'uses' => 'SendGiftController@create',
]);

Route::post('send-gift', [
    'as' => 'specialgift.send.store',
    'uses' => 'SendGiftController@store',
    'middleware' => ProtectAgainstSpam::class,
]);
