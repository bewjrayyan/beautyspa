<?php

use Illuminate\Support\Facades\Route;

Route::get('gift-voucher-submissions', [
    'as' => 'admin.gift_voucher_submissions.index',
    'uses' => 'GiftVoucherSubmissionController@index',
    'middleware' => 'can:admin.gift_voucher_submissions.index',
]);

Route::middleware('can:admin.gift_voucher_submissions.settings')->group(function () {
    Route::get('gift-voucher-submissions/content', [
        'as' => 'admin.gift_voucher_submissions.content',
        'uses' => 'GiftVoucherPageController@content',
    ]);

    Route::get('gift-voucher-submissions/design', [
        'as' => 'admin.gift_voucher_submissions.design',
        'uses' => 'GiftVoucherPageController@design',
    ]);

    Route::get('gift-voucher-submissions/settings', [
        'as' => 'admin.gift_voucher_submissions.settings',
        'uses' => 'GiftVoucherPageController@settings',
    ]);

    Route::put('gift-voucher-submissions/settings', [
        'as' => 'admin.gift_voucher_submissions.settings.update',
        'uses' => 'GiftVoucherPageController@update',
    ]);
});
