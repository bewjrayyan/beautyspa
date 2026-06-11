<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('account/loyalty', [
        'uses' => 'AccountLoyaltyController@index',
        'as' => 'account.loyalty.index',
    ]);

    Route::post('account/loyalty/stamp-cards/{wallet}/redeem', [
        'uses' => 'AccountLoyaltyController@redeemStamp',
        'as' => 'account.loyalty.stamp_cards.redeem',
    ]);

    Route::get('cart/loyalty/quote', [
        'uses' => 'CartLoyaltyController@quote',
        'as' => 'cart.loyalty.quote',
    ]);

    Route::post('cart/loyalty', [
        'uses' => 'CartLoyaltyController@store',
        'as' => 'cart.loyalty.store',
    ]);

    Route::delete('cart/loyalty', [
        'uses' => 'CartLoyaltyController@destroy',
        'as' => 'cart.loyalty.destroy',
    ]);
});
