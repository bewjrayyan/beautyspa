<?php

use Illuminate\Support\Facades\Route;

Route::get('shipping-classes', [
    'as' => 'admin.shipping_classes.index',
    'uses' => 'ShippingClassController@index',
    'middleware' => 'can:admin.shipping_classes.index',
]);

Route::get('shipping-classes/index/table', [
    'as' => 'admin.shipping_classes.table',
    'uses' => 'ShippingClassController@table',
    'middleware' => 'can:admin.shipping_classes.index',
]);

Route::get('shipping-classes/create', [
    'as' => 'admin.shipping_classes.create',
    'uses' => 'ShippingClassController@create',
    'middleware' => 'can:admin.shipping_classes.create',
]);

Route::post('shipping-classes', [
    'as' => 'admin.shipping_classes.store',
    'uses' => 'ShippingClassController@store',
    'middleware' => 'can:admin.shipping_classes.create',
]);

Route::get('shipping-classes/{id}/edit', [
    'as' => 'admin.shipping_classes.edit',
    'uses' => 'ShippingClassController@edit',
    'middleware' => 'can:admin.shipping_classes.edit',
]);

Route::put('shipping-classes/{id}', [
    'as' => 'admin.shipping_classes.update',
    'uses' => 'ShippingClassController@update',
    'middleware' => 'can:admin.shipping_classes.edit',
]);

Route::delete('shipping-classes/{ids?}', [
    'as' => 'admin.shipping_classes.destroy',
    'uses' => 'ShippingClassController@destroy',
    'middleware' => 'can:admin.shipping_classes.destroy',
]);
