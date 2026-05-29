<?php

use Illuminate\Support\Facades\Route;

Route::get('gift-voucher-submissions', [
    'as' => 'admin.gift_voucher_submissions.index',
    'uses' => 'GiftVoucherSubmissionController@index',
    'middleware' => 'can:admin.gift_voucher_submissions.index',
]);
