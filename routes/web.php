<?php
use Illuminate\Support\Facades\DB;
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

Route::get('/', function () {
	$ml = DB::table('ml_data')->get();
	//return $ml;
    return view('welcome', ['data' => $ml]);
});



Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();
Route::post('import/asin', 'ProductsController@importProducts')->name('import.products');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/import', function(){
	return view('importProducts');
});

Route::get('/scrapper', 'ScrapperController@index')->name('scrap');