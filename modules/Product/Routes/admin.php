<?php

use Illuminate\Support\Facades\Route;

Route::get('products', [
    'as' => 'admin.products.index',
    'uses' => 'ProductController@index',
    'middleware' => 'can:admin.products.index',
]);

Route::get(
    'products/create',
    [
        'as' => 'admin.products.create',
        'uses' => 'ProductController@create',
        'middleware' => 'can:admin.products.create',
    ]
);

Route::post('products', [
    'as' => 'admin.products.store',
    'uses' => 'ProductController@store',
    'middleware' => 'can:admin.products.create',
]);

Route::post('products/{id}/clone', [
    'as' => 'admin.products.clone',
    'uses' => 'ProductController@clone',
    'middleware' => 'can:admin.products.create',
]);

Route::put('products/{id}/status', [
    'as' => 'admin.products.status',
    'uses' => 'ProductController@updateStatus',
    'middleware' => 'can:admin.products.edit',
]);

Route::get('products/{id}/edit', [
    'as' => 'admin.products.edit',
    'uses' => 'ProductController@edit',
    'middleware' => 'can:admin.products.edit',
]);

Route::put('products/{id}', [
    'as' => 'admin.products.update',
    'uses' => 'ProductController@update',
    'middleware' => 'can:admin.products.edit',
]);

Route::delete('products/{ids}', [
    'as' => 'admin.products.destroy',
    'uses' => 'ProductController@destroy',
    'middleware' => 'can:admin.products.destroy',
]);

Route::get('products/index/table', [
    'as' => 'admin.products.table',
    'uses' => 'ProductController@table',
    'middleware' => 'can:admin.products.index',
]);

Route::delete('search-terms', [
    'as' => 'admin.search_terms.destroy',
    'uses' => 'SearchTermController@destroy',
    'middleware' => 'can:admin.products.index',
]);
