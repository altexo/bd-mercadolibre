<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ml_data;
use DB;
use App\Products;
class ProductsDBController extends Controller
{

	public function __construct(){
		  $this->middleware('auth');
	}
	
	public function index(){
		 $products = Ml_data::select('ml_data.id as ml_data_id','products.id as product_id','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
    	->join('products','ml_data.id','=','products.ml_data_id')
    	->join('provider', 'products.provider_id', '=', 'provider.id')
    	->where('products.provider_id','!=',1)
    	->where('provider.provider_status_id','=',1)
		->paginate(20);

		// $products = DB::select("SELECT count(ml.id) FROM ml_data ml
		// INNER JOIN products pr on pr.ml_data_id = ml.id
		// INNER JOIN provider p on pr.provider_id = p.id
		// where p.provider_status_id = 1");
		return view('products.view', ['products' => $products]);
	}

	public function getById($id){
		$product = Ml_data::join('products','ml_data.id','=','products.ml_data_id')->where('ml_data.id',$id)->first();

		return response()->json($product);
	}
	public function updateById(Request $request){
		$product = Products::where('id',$request->product_id)->first();

		$product->title = $request->title;
		$product->save();

		return response()->json('Se actualizo correctamente');
	}
//	
   
}
