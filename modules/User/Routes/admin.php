<?php

use Illuminate\Support\Facades\Route;

Route::get('login', 'AuthController@getLogin')->name('admin.login');
Route::post('login', 'AuthController@postLogin')
    ->middleware('throttle:auth')
    ->name('admin.login.post');

Route::post('login/whatsapp/send-otp', 'BeauticianWhatsAppOtpAuthController@sendOtp')
    ->middleware('throttle:6,1')
    ->name('admin.login.whatsapp.send_otp');
Route::post('login/whatsapp/verify-otp', 'BeauticianWhatsAppOtpAuthController@verifyOtp')
    ->middleware('throttle:10,1')
    ->name('admin.login.whatsapp.verify_otp');

Route::get('logout', 'AuthController@getLogout')->name('admin.logout');

Route::get('password/reset', 'AuthController@getReset')->name('admin.reset');
Route::post('password/reset', 'AuthController@postReset')
    ->middleware('throttle:auth')
    ->name('admin.reset.post');
Route::get('password/reset/{email}/{code}', 'AuthController@getResetComplete')->name('admin.reset.complete');
Route::post('password/reset/{email}/{code}', 'AuthController@postResetComplete')
    ->middleware('throttle:auth')
    ->name('admin.reset.complete.post');

Route::get('users', [
    'as' => 'admin.users.index',
    'uses' => 'UserController@index',
    'middleware' => 'can:admin.users.index',
]);

Route::get('users/create', [
    'as' => 'admin.users.create',
    'uses' => 'UserController@create',
    'middleware' => 'can:admin.users.create',
]);

Route::post('users', [
    'as' => 'admin.users.store',
    'uses' => 'UserController@store',
    'middleware' => 'can:admin.users.create',
]);

Route::post('users/enroll-loyalty', [
    'as' => 'admin.users.enroll_loyalty',
    'uses' => 'UserController@enrollLoyaltyMembers',
    'middleware' => 'can:admin.loyalty.members.enroll',
]);

Route::post('users/enroll-loyalty/{ids}', [
    'as' => 'admin.users.enroll_loyalty_bulk',
    'uses' => 'UserController@enrollLoyaltyMembersBulk',
    'middleware' => 'can:admin.loyalty.members.enroll',
])->where('ids', '[0-9,]+');

Route::get('users/{id}/edit', [
    'as' => 'admin.users.edit',
    'uses' => 'UserController@edit',
    'middleware' => 'can:admin.users.edit',
]);

Route::put('users/{id}/edit', [
    'as' => 'admin.users.update',
    'uses' => 'UserController@update',
    'middleware' => 'can:admin.users.edit',
]);

Route::delete('users/{ids?}', [
    'as' => 'admin.users.destroy',
    'uses' => 'UserController@destroy',
    'middleware' => 'can:admin.users.destroy',
]);

Route::get('users/index/table', [
    'as' => 'admin.users.table',
    'uses' => 'UserController@table',
    'middleware' => 'can:admin.users.index',
]);

Route::get('users/{id}/reset-password', [
    'as' => 'admin.users.reset_password',
    'uses' => 'UserResetPasswordController@store',
    'middleware' => 'can:admin.users.edit',
]);

Route::get('roles', [
    'as' => 'admin.roles.index',
    'uses' => 'RoleController@index',
    'middleware' => 'can:admin.roles.index',
]);

Route::get('roles/index/table', [
    'as' => 'admin.roles.table',
    'uses' => 'RoleController@table',
    'middleware' => 'can:admin.roles.index',
]);

Route::get('roles/create', [
    'as' => 'admin.roles.create',
    'uses' => 'RoleController@create',
    'middleware' => 'can:admin.roles.create',
]);

Route::post('roles', [
    'as' => 'admin.roles.store',
    'uses' => 'RoleController@store',
    'middleware' => 'can:admin.roles.create',
]);

Route::get('roles/{id}/edit', [
    'as' => 'admin.roles.edit',
    'uses' => 'RoleController@edit',
    'middleware' => 'can:admin.roles.edit',
]);

Route::put('roles/{id}/edit', [
    'as' => 'admin.roles.update',
    'uses' => 'RoleController@update',
    'middleware' => 'can:admin.roles.edit',
]);

Route::delete('roles/{ids?}', [
    'as' => 'admin.roles.destroy',
    'uses' => 'RoleController@destroy',
    'middleware' => 'can:admin.roles.destroy',
]);

// Profile
Route::get('profile', [
    'as' => 'admin.profile.edit',
    'uses' => 'ProfileController@edit',
]);

Route::put('profile', [
    'as' => 'admin.profile.update',
    'uses' => 'ProfileController@update',
]);
