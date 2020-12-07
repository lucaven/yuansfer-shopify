<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'DashboardController@index')->middleware(['auth.shopify'])->name('home');
Route::post('/', 'DashboardController@store')->middleware(['auth.shopify']);


Route::get('/callback/order/{id}', 'CallbackController@index')->name('callback');
Route::post('/callback/ipn', 'CallbackController@ipn')->name('callback.ipn');

Route::get('/assets/js/checkout.js', 'ProxyController@script')->middleware('auth.proxy');
Route::get('/assets/checkout/{id}', 'ProxyController@doPayment')->middleware('auth.proxy');
