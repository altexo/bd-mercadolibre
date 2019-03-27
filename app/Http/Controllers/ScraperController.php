<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Ml_data;
use App\Provider;
use App\Products;
use App\tags;
use App\Pictures;
use App\Shipping;
use DB;
use Date;

class ScraperController extends Controller
{
    public function index(){
    	$asin = 'B00K5NBQGO';
    	$client = new Client([
			'base_uri'=> 'https://api.scrapehero.com/amaz_mx/product-details/?asin='.$asin.'&apikey=59242154b4e89e0fd599213326a2d4f78dba436eba0c70b19e33fccb',
			'http_errors' => false
    	]); 
		$response = $client->request('GET');

		//$body = $client->getBody();
		//$response = new R;
		$response = $response->getBody()->getContents();
		$res = json_decode($response, true);//Arreglo de producto mediante asin

		//Convertimos las imagenes en una lista de arreglos
	//	$png_img = "";
		$pictures_array = [];
		 foreach ($res['images'] as $img) {        
		 // 	if (exif_imagetype($img) != IMAGETYPE_JPEG) {
			//     continue;
			// }
			$img_format = substr($img, -3);
			if ($img_format == "png") {
				continue;
			}
			// return response()->json(['string']);
            array_push($pictures_array, ['source' => $img]);
         }                           
        //Codificamos el arreglo de imagenes a json 
        $pictures_array = json_encode($pictures_array);
		//Transform price
		$providerPrice = $res['price'];
		//Quitamos el signo de moneda del precio
		$providerPrice = substr($providerPrice, 1);
		//Convertims a peso y aumentamos el precio del producto
		$providerPrice = 1.60*($providerPrice*20);

		//Test Line
		return response()->json(['Nothing found' => $pictures_array ]);
		//End test line

		//Comienza transaccion de captura de nuevo producto 
		try {
			DB::transaction(function () use($providerPrice, $res, $pictures_array, $asin) {
	    		//Create new provider object
				$provider = new Provider;
				$provider->provider_link = $res['url'];
				$provider->price = substr($res['price'],1);
				$provider->asin = $asin;
				$provider->save();

				//Create new ml_data object
				$ml_data = new Ml_data;
				$ml_data->category_id = 'MLM1132';
				$ml_data->price = $providerPrice;
				$ml_data->available_quantity = 1;
				$ml_data->currency_id ='MXN';
				$ml_data->buying_mode = 'buy_it_now';
				$ml_data->listing_type_id = 'gold_pro';
				$ml_data->description = $res['small_description'];
				$ml_data->accepts_mercadopago = 1;
				$ml_data->save();

				//Create new producs object
				$products = new Products;
				$products->title = $res['name'];
				$products->type_id = 1;
				$products->provider_id = $provider->id;
				$products->ml_data_id = $ml_data->id;
				$products->save();

				//Create pictures object
				$pictures = new Pictures;
				$pictures->url = $pictures_array;
				$pictures->ml_data_id = $ml_data->id;
				$pictures->save();

				//Create shipping Object
				$shipping = new Shipping;
				$shipping->full_atts = '{"mode":"me2","free_methods":[{"id":"501245","rule":{"default":"1","free_mode":"country","free_shipping_flag":"1"}}],"tags":["mandatory_free_shipping"],"local_pick_up":"1","free_shipping":"1","logistic_type":"drop_off","store_pick_up":"0"}';
				$shipping->ml_data_id = $ml_data->id;
				$shipping->save();

				//Create Tags object
				$tags = new tags;
				$tags->tags_object = '["brand_verified","immediate_payment","cart_eligible"]';
				$tags->ml_data_id = $ml_data->id;
				$tags->save();

				//return $commited = ['provider' => $provider, 'ml'=> $ml_data];
			}, 1);
		} catch (Exception $e) {
			dd($e);
		}
		

    	return ['Succeed'];
    }

    public function updateProductsPrice(){
    	 $response_array = [];
    	 $errors = [];


    		$products = Ml_data::select('ml_data.id as ml_data_id','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
    			->join('products','ml_data.id','=','products.ml_data_id')
    			->join('provider', 'products.provider_id', '=', 'provider.id')
    			->where('products.provider_id','!=',1)
    			//->where('provider.provider_status_id','=',1)
    			//->where('provider.asin','!=', "")
    			// ->whereRaw('date(ml_data.updated_at) != "2019-02-01" AND date(ml_data.updated_at) != "2019-02-02" ')
    			//->take(3)
    			->get();
     		$count = count($products);
   return response()->json($count);
    		if ($products != NULL) {

    			foreach ($products as $product) {
    					$asin = $product->asin;
	    				if ($asin == "") {
	    					continue;
	    				}
	    			   
				    	$client = new Client([
								'base_uri' => 'https://api.keepa.com/product?key=d8ukh5gnd7qfrsl3n7s6s9e9lj8k9v7k2bq3f8l9hgpamve59rnov65j4co73ko2&domain=11&asin='.$asin.'&stats=24&history=0',
				    		//'base_uri'=> 'https://api.scrapehero.com/amaz_mx/product-details/?asin='.$asin.'&apikey=59242154b4e89e0fd599213326a2d4f78dba436eba0c70b19e33fccb',
				    		'http_errors' => false
				    		//	'headers' => ['X-Mashape-Key' => 'zg0snQgYOimshgrnP0Mx5m9O3vlQp1cjQX1jsncrcfBCh3zcps', 'Accept' => 'application/json']
				    	]); 
						$response = $client->request('GET');

						$response = $response->getBody()->getContents();
						//Arreglo de producto mediante asin
						$res = json_decode($response, true);
						$stats = $res['products'];
						$price = $stats[0]['stats']['current'][0];
					//	$current = $stats->current;
			

						//array_push($response_array, ['price' => $price, 'asin' => $asin, 'mlid' => $product->ml_data_id]);
					
					//	continue;
						// if (empty($res)) {
						// 	$this->updateProductStatus($product->provider_id);
						// 	array_push($errors, ['title'=>$product->title,'empty_res'=>$asin]);
						// }
						// if ($res == null) {
						// 	$this->updateProductStatus($product->provider_id);
						// 	array_push($errors, ['title'=>$product->title,'null_res'=>$asin]);
						// 	continue;
						// }
				
						// if (array_key_exists("price",$res))
						// {
						 
						// }
						// else
						// {
						// 	$this->updateProductStatus($product->provider_id);
						// 	array_push($errors, ['title'=>$product->title, 'price_not_found'=>$asin]);
						//   	continue;
						// }
						//verificamos el precio no venga en null
						if ($price == -1) {
							$this->updateProductStatus($product->provider_id);
							array_push($errors, ['title'=>$product->title,'No disponible en stock'=>$asin]);
							continue;
						}
						//Transform price
						$decimalPrice = sprintf('%.2f', $price / 100);
						$providerPrice = $decimalPrice;
						//Quitamos el signo de moneda del precio
						//$providerPrice = substr($providerPrice, 1);
					//	$providerPrice = str_replace(',', '', $providerPrice);
						//$providerPrice = intval($providerPrice);
						//Convertims a peso y aumentamos el precio del producto
						$sell_price = 1.60 * $providerPrice;
						$sell_price = round($sell_price);
						
						// $pictures_array = [];
						//  foreach ($res['images'] as $img) {  
						// 	//https://images-na.ssl-images-amazon.com/images/I/
				    //         array_push($pictures_array, ['source' => $img]);
				    //      }    
				    //      //Codificamos el arreglo de imagenes a json 
        		// 		$pictures_array = json_encode($pictures_array);
						//Comienza transaccion de captura de nuevo producto 
						try {
							$transaction = DB::transaction(function() use($providerPrice, $product, $sell_price){
								$ml_data = Ml_data::where('id',$product->ml_data_id)->first();
								$ml_data->price = $sell_price;
								$ml_data->available_quantity = 99;
								$ml_data->save();

								$provider = Provider::where('id',$product->provider_id)->first();
								//$provider->provider_link = $res['url'];
								$provider->price = $providerPrice;
								$provider->provider_status_id = 1;
								$provider->save();

								// $pictures = Pictures::where('ml_data_id', $product->ml_data_id)->first();
								// $pictures->url = $pictures_array;
								// $pictures->save();
								
								return $transaction = ['provider'=> $provider, 'ml_data'=> $ml_data->id];

							});
						} catch (Exception $e) {
							$response = $e;
						}
							array_push($response_array, $transaction);
							sleep(65);
					}
				}
					//$response = $products;
    	return response()->json(['ok' => $response_array,'errors'=>$errors]);
    		}
	public function updateProductsContent(){
				$response_array = [];
				$errors = [];
	   
	   
				   $products = Ml_data::select('ml_data.id as ml_data_id','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
					   ->join('products','ml_data.id','=','products.ml_data_id')
					   ->join('provider', 'products.provider_id', '=', 'provider.id')
					   ->where('products.provider_id','!=',1)
					   //->where('provider.provider_status_id','=',1)
					   //->where('provider.asin','!=', "")
					   // ->whereRaw('date(ml_data.updated_at) != "2019-02-01" AND date(ml_data.updated_at) != "2019-02-02" ')
					   ->take(3)
					   ->get();
					//$count = count($products);
		  //return response()->json($count);
				   if ($products != NULL) {
	   
					   foreach ($products as $product) {
							   $asin = $product->asin;
							   if ($asin == "") {
								   continue;
							   }
							  
							   $client = new Client([
									'base_uri' => 'https://api.keepa.com/product?key=5p9iqnjcbrjj6a7avab554mn9ok6khrjtb5qie8r7e2b6e9fki8gc4i9c9lrkm5s&domain=11&asin='.$asin.'&stats=24&history=0',
								   	'http_errors' => false
							   ]); 
							   $response = $client->request('GET');
	   
							   $response = $response->getBody()->getContents();
							   //Arreglo de producto mediante asin
							   $res = json_decode($response, true);
							  
							   $stats = $res['products'];
							   $price = $stats[0]['stats']['current'][0];
							   //verificamos el precio no venga en null
							   if ($price == -1) {
								   $this->updateProductStatus($product->provider_id);
								   array_push($errors, ['title'=>$product->title,'No disponible en stock'=>$asin]);
								   continue;
							   }
							   //Transform price
							   $decimalPrice = sprintf('%.2f', $price / 100);
							   $providerPrice = $decimalPrice;
							   $sell_price = 1.60 * $providerPrice;
							   $sell_price = round($sell_price);
							   
							   $imgs = $stats['imagesCSV'];
							   $imgs = explode(",", $imgs);
							    $pictures_array = [];
							     foreach ($imgs as $img) {  
									
									$image = 'https://images-na.ssl-images-amazon.com/images/I/'.$img;
						          	array_push($pictures_array, ['source' => $image]);
						      	}    
						   //      //Codificamos el arreglo de imagenes a json 
								$pictures_array = json_encode($pictures_array);
								return response()->json($pictures_array);
							   //Comienza transaccion de captura de nuevo producto 
							   try {
								   $transaction = DB::transaction(function() use($providerPrice, $product, $sell_price){
									   $ml_data = Ml_data::where('id',$product->ml_data_id)->first();
									   $ml_data->price = $sell_price;
									   $ml_data->available_quantity = 99;
									   $ml_data->save();
	   
									   $provider = Provider::where('id',$product->provider_id)->first();
									   //$provider->provider_link = $res['url'];
									   $provider->price = $providerPrice;
									   $provider->provider_status_id = 1;
									   $provider->save();
	   
									   // $pictures = Pictures::where('ml_data_id', $product->ml_data_id)->first();
									   // $pictures->url = $pictures_array;
									   // $pictures->save();
									   
									   return $transaction = ['provider'=> $provider, 'ml_data'=> $ml_data->id];
	   
								   });
							   } catch (Exception $e) {
								   $response = $e;
							   }
								   array_push($response_array, $transaction);
								   sleep(65);
						   }
					   }
						   //$response = $products;
			   return response()->json(['ok' => $response_array,'errors'=>$errors]);
				   }
	   
    	private function updateProductStatus($id){
    		$provider = Provider::where('id',$id)->first();
			$provider->provider_status_id = 2;
			$provider->save();

    	}
    	public function testCall(){
				$asin = 'B009163BO6';
    		try {
    			$client = new Client([
					//	'verify' => 'C:\Users\SECSASERVER\Documents\dev\drop-shipping\bd-mercadolibre',
					'base_uri' => 'https://api.keepa.com/product?key=5p9iqnjcbrjj6a7avab554mn9ok6khrjtb5qie8r7e2b6e9fki8gc4i9c9lrkm5s&domain=11&asin='.$asin.'&stats=24&history=0',
					   'http_errors' => false
			   ]); 
			   $response = $client->request('GET');
	   
			   $response = $response->getBody()->getContents();
			   //Arreglo de producto mediante asin
			   $res = json_decode($response, true);
			  
			   $stats = $res['products'];
						return response()->json($stats);
			   $imgs = $stats->imagesCSV;
			   $imgs = explode(",", $imgs);
				$pictures_array = [];
				 foreach ($imgs as $img) {  
					
					$image = 'https://images-na.ssl-images-amazon.com/images/I/'.$img;
					  array_push($pictures_array, ['source' => $image]);
				  }  
    		} catch (Exception $e) {
    				return response()->json('err');
    		}
    		 	
			 
		//	$pictures_array = $pictures_array;
			return response()->json($pictures_array);
    	}

		}
		
		