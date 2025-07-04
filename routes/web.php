<?php

use Illuminate\Http\Request;
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
Route::get('transactions/{address}/{event_id}', 'TransactionsController@details')->name('transactions.single');

Route::get('/downloads/erc20', 'CsvExportController@etherscan')->name('transactions.download.etherscan');
Route::get('/downloads/erc721-erc1155', 'CsvExportController@opensea')->name('transactions.download.opensea');

Route::get('opensea', function (Request $request) {
    return view('opensea');
});
