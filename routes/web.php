<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'HomeController@index')->name('home');
Route::view('/faqs', 'faqs')->name('faqs');
Route::post('/subscribe', 'SubscriptionsController@subscribe')->name('subscribe');

Route::get('/transactions', 'TransactionsController@index')->name('transactions');
Route::get('/transactions/{wallet}/{event_id}', 'TransactionsController@details')->name('transactions.single');
Route::view('/details', 'transactions.details');
