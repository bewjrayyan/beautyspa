<?php

use Illuminate\Support\Facades\Route;

Route::get('secure/order/{order}/payment-proof/{file}', 'OrderPaymentProofController@show')
    ->middleware(['signed', 'throttle:30,1'])
    ->name('order.payment_proofs.temporary');

Route::get('secure/order/{order}/document/{type}/{fingerprint}', 'OrderTemporaryDocumentController@show')
    ->middleware(['signed', 'throttle:30,1'])
    ->name('order.documents.temporary');
