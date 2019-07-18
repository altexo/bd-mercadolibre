<?php
use Illuminate\Support\Facades\DB;
use App\Mail\ProductsUpdatesNotification;
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

 Route::get('/', 'HomeController@index');


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
})->name('import.products.view');
Route::post('import/asin', 'ProductsController@importProducts')->name('import.products');
Route::post('import/update/asin','importsController@importAsinUpdate')->name('import.update.asins');
Route::post('import/new-asins','importsController@importNewAsins')->name('import.new.asins');
//End imports
Route::post('products/delete', 'ProductsController@deleteProduct')->name('products.delete');
Route::get('products/new', 'ProductsController@newProductview')->name('proucts.addNew');
Route::post('products/new/create', 'ProductsController@create')->name('proucts.create');
Route::get('/home', 'HomeController@index')->name('home');


Route::get('/scrapper', 'ScrapperController@index')->name('scrap');
Route::get('products/update/price/ml','ProductsController@updateProductsPrices')->name('get.products');


//Test Route
// Route::get('test/mail', function(){
// 	$msj = "El precio de los productos fueron actualizados correctamente, Total actualizados: 202";
// 	Mail::to('alejandro.riosyb@gmail.com')->send(new ProductsUpdatesNotification($msj));
// });