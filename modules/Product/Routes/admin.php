<?php

use Illuminate\Support\Facades\Route;

Route::get('consultation-forms', [
    'as' => 'admin.consultation_forms.index',
    'uses' => '\Modules\Account\Http\Controllers\Admin\ConsultationTemplateController@index',
    'middleware' => 'can:admin.consultation_forms.index',
]);

Route::get('consultation-forms/{template}/edit', [
    'as' => 'admin.consultation_forms.edit',
    'uses' => '\Modules\Account\Http\Controllers\Admin\ConsultationTemplateController@edit',
    'middleware' => 'can:admin.consultation_forms.edit',
]);

Route::get('consultation-forms/{template}', [
    'as' => 'admin.consultation_forms.show',
    'uses' => '\Modules\Account\Http\Controllers\Admin\ConsultationTemplateController@show',
    'middleware' => 'can:admin.consultation_forms.index',
]);

Route::put('consultation-forms/{template}', [
    'as' => 'admin.consultation_forms.update',
    'uses' => '\Modules\Account\Http\Controllers\Admin\ConsultationTemplateController@update',
    'middleware' => 'can:admin.consultation_forms.edit',
]);

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

Route::put('products/bulk-status', [
    'as' => 'admin.products.bulk_status',
    'uses' => 'ProductController@bulkUpdateStatus',
    'middleware' => 'can:admin.products.edit',
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
