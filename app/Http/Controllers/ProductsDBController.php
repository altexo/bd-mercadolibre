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
		 $products = Ml_data::select('ml_data.id as ml_data_id','products.id as product_id' ,'products.margin_sale','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
    	->join('products','ml_data.id','=','products.ml_data_id')
    	->join('provider', 'products.provider_id', '=', 'provider.id')
    	->where('products.provider_id','!=',1)
    	->where('provider.provider_status_id','=',1)
		->paginate(20);
		//$products = Ml_data::search('multipet')->with('products')->get();
		//return $products;
		return view('products.view', ['products' => $products]);
	}
	
	public function search(Request $r){
		$products = Ml_data::search($r->search)->select('ml_data.id as ml_data_id','products.id as product_id' ,'products.margin_sale','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
    	->join('products','ml_data.id','=','products.ml_data_id')
    	->join('provider', 'products.provider_id', '=', 'provider.id')
    	->where('products.provider_id','!=',1)
    	->where('provider.provider_status_id','=',1)
		->paginate(20);

		return view('products.view', ['products' => $products]);
	}

	public function getById($id){
		$product = Ml_data::join('products','ml_data.id','=','products.ml_data_id')->where('ml_data.id',$id)->first();

		return response()->json($product);
	}
	public function updateById(Request $request){
		$msj = "";
		$error = false;
		$product = Products::where('id',$request->product_id)->first();
		try {
			$product->title = $request->title;
			$product->margin_sale = $request->margin_sale;
			$product->save();
			$msj = "Se actualizo correctamente";
		} catch (PDOException $e ) {
			$msj="Error al actualizar producto";
			$error=true;
		}


			# code...
		return response()->json(['error' => $error, 'msj'=>$msj]);
	}
//	

}
