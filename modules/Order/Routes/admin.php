<?php

use Illuminate\Support\Facades\Route;

Route::get('orders', [
    'as' => 'admin.orders.index',
    'uses' => 'OrderController@index',
    'middleware' => 'can:admin.orders.index',
]);

Route::get('orders/{id}', [
    'as' => 'admin.orders.show',
    'uses' => 'OrderController@show',
    'middleware' => 'can:admin.orders.show',
]);

Route::put('orders/{id}', [
    'as' => 'admin.orders.update',
    'uses' => 'OrderController@update',
    'middleware' => 'can:admin.orders.edit',
]);

Route::delete('orders/{ids}', [
    'as' => 'admin.orders.destroy',
    'uses' => 'OrderController@destroy',
    'middleware' => 'can:admin.orders.destroy',
]);

Route::delete('orders/{ids}/force', [
    'as' => 'admin.orders.force_destroy',
    'uses' => 'OrderController@forceDestroy',
    'middleware' => 'can:admin.orders.destroy',
]);

Route::get('orders/index/table', [
    'as' => 'admin.orders.table',
    'uses' => 'OrderController@table',
    'middleware' => 'can:admin.orders.index',
]);

Route::put('orders/{order}/status', [
    'as' => 'admin.orders.status.update',
    'uses' => 'OrderStatusController@update',
    'middleware' => 'can:admin.orders.edit',
]);

Route::put('orders/{order}/payment-status', [
    'as' => 'admin.orders.payment_status.update',
    'uses' => 'OrderPaymentStatusController@update',
    'middleware' => 'can:admin.orders.edit',
]);

Route::post('orders/{order}/email', [
    'as' => 'admin.orders.email.store',
    'uses' => 'OrderEmailController@store',
    'middleware' => 'can:admin.orders.show',
]);

Route::post('orders/{order}/whatsapp/invoice', [
    'as' => 'admin.orders.whatsapp.invoice',
    'uses' => 'OrderWhatsAppController@sendInvoice',
    'middleware' => 'can:admin.orders.show',
]);

Route::post('orders/{order}/whatsapp/receipt', [
    'as' => 'admin.orders.whatsapp.receipt',
    'uses' => 'OrderWhatsAppController@sendReceipt',
    'middleware' => 'can:admin.orders.show',
]);

Route::get('orders/{order}/print', [
    'as' => 'admin.orders.print.show',
    'uses' => 'OrderPrintController@show',
    'middleware' => 'can:admin.orders.show',
]);

Route::get('orders/{order}/receipt', [
    'as' => 'admin.orders.receipt.show',
    'uses' => 'OrderPrintController@receipt',
    'middleware' => 'can:admin.orders.show',
]);
