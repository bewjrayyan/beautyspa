<?php

use Illuminate\Support\Facades\Route;

Route::get('sitemap', 'SitemapController@index')->name('sitemap');
