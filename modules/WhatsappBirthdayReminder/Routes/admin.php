<?php

use Illuminate\Support\Facades\Route;

Route::get('whatsapp-birthday', [
    'as' => 'admin.whatsapp_birthday.index',
    'uses' => 'LogController@index',
    'middleware' => 'can:admin.whatsapp_birthday.index',
]);

Route::post('whatsapp-birthday/send', [
    'as' => 'admin.whatsapp_birthday.send',
    'uses' => 'LogController@send',
    'middleware' => 'can:admin.whatsapp_birthday.send',
]);
