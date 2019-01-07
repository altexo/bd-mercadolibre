<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/products/create', 'ProductsController@create');

Route::post('dummy/test', 'ProductsController@dummy');
Route::get('dummy/get', function(){
	return response()->json(['Success' => 'ok']);
});
Route::get('products','ProductsController@publishAll');

Route::get('products/update', 'ScraperController@updateProductsPrice');