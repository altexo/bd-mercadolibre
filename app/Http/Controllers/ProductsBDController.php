<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductsBDController extends Controller
{

	public function __construct(){
		  $this->middleware('auth');
	}
	
	public function index(){
		 $products = Ml_data::select('ml_data.id as ml_data_id','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
    	->join('products','ml_data.id','=','products.ml_data_id')
    	->join('provider', 'products.provider_id', '=', 'provider.id')
    	->where('products.provider_id','!=',1)
    	->where('provider.provider_status_id','=',1)
    	->paginate(20);
	}
	return $products;
   
}
