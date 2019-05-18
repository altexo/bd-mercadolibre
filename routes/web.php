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

route::get('/scrap/get-by-asin', 'ScraperController@index');

//Route::get('/home', 'HomeController@index')->name('home');
Route::get('/products/view', 'ProductsDBController@index')->name('products.view');
Route::get('/products/edit/{id}', 'ProductsDBController@getById')->name('products.get.product');
Route::post('products/update', 'ProductsDBController@updateById')->name('products.update');
Route::get('/ml', 'HomeController@index')->name('home');
Auth::routes();
// Route::get('/register', 'HomeController@index');
//Imports
Route::get('/import', function(){
	return view('importProducts');
});
Route::post('import/asin', 'ProductsController@importProducts')->name('import.products');
Route::post('import/update/asin','importsController@importAsinUpdate')->name('import.update.asins');
Route::post('import/new-asins','importsController@importNewAsins')->name('import.new.asins');
//End imports

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/scrapper', 'ScrapperController@index')->name('scrap');
Route::get('products/update/price/ml','ProductsController@updateProductsPrices')->name('get.products');
