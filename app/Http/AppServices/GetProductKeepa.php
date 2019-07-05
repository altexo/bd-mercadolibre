<?php
namespace App\Http\AppServices;
use App\Provider;
use App\Ml_data;
use App\Products;
use App\Pictures;
use App\tags;
use App\Shipping;
use GuzzleHttp\Client;
use GuzzleHttp;
use DB;
class GetProductKeepa{
  
    public function scrapProduct($asin){
	    if ($asin == "") {		
            return ['error'=> true, 'msj'=> 'El Asin esta vacio'];
		}
		
		$provider = Provider::where('asin', $asin)->first();
		if ($provider != null) {
            return ['error'=> true, 'msj'=> 'El ASIN ya exixte!'];
		}
	    			   
		$client = new Client([
            'base_uri' => 'https://api.keepa.com/product?key='.ENV('KEEPA_TOKEN').'&domain=11&asin='.$asin.'&stats=24&history=0',
            'http_errors' => false
                   
        ]); 
            
        $response = $client->request('GET');
        $response = $response->getBody()->getContents();
        $res = json_decode($response, true);
		$validation = $this->validateKeepaResponse($res);
		if ($validation == false) {
            return ['error'=> true, 'msj'=> 'Error al consultar en Keepa!'];
		}
        
        $stats = $validation;
		$price = $stats[0];
        $priceThirdPartySeller = $stats[1];
        if ($price == -1) {
            if ($priceThirdPartySeller == -1) {
				return ['error'=> true, 'msj'=> 'No disponible en stock!'];
            }else{
                $price = $priceThirdPartySeller;
            }
        }
        
        $title = $res['products'][0]['title'];
		$title = substr($title,0,60);
		$base_category = $this->predictCategoryML($title);
		if ($base_category == null) {
			return ['error'=> true, 'msj'=> 'No se predijo categoria: '.$title ];
		}
		$imgs = $res['products'][0];
		$descripcion = $stats[0]['description'];
        $imgs = $imgs['imagesCSV'];
        $imgs = explode(",", $imgs);
        $pictures_array = [];
        foreach ($imgs as $img) {     
            $image = 'https://images-na.ssl-images-amazon.com/images/I/'.$img;
            array_push($pictures_array, ['source' => $image]);
        }  
        
        $decimalPrice = sprintf('%.2f', $price / 100);
        $providerPrice = $decimalPrice;
        $sell_price = round($providerPrice);
        $pictures_array = json_encode($pictures_array);
								
        try {
				$data = DB::transaction(function () use($providerPrice, $sell_price, $pictures_array, $asin, $title, $base_category, $descripcion) {
						$title = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $title);
			    		//Create new provider object
						$provider = new Provider;
						//$provider->provider_link = $res['url'];
						$provider->price = $providerPrice;
						$provider->asin = $asin;
						$provider->provider_status_id = 4;
						$provider->save();
						//Create new ml_data object
						$ml_data = new Ml_data;
						$ml_data->category_id = $base_category;
						$ml_data->price = $sell_price;
						$ml_data->available_quantity = 99;
						$ml_data->currency_id ='MXN';
						$ml_data->buying_mode = 'buy_it_now';
						$ml_data->listing_type_id = 'gold_pro';
						$ml_data->description = $descripcion;
						$ml_data->accepts_mercadopago = 1;
						$ml_data->save();
						//Create new producs object
						$products = new Products;
						$products->title = $title;
                        $products->type_id = 1;
                        $products->margin_sale = null;
						$products->provider_id = $provider->id;
						$products->ml_data_id = $ml_data->id;
						$products->save();
						// $products = DB::table('products')->insert(
						// 	array('title' => $title, 'type_id' => 1, 'margin_sale' => null, 'provider_id' => $provider->id, 'ml_data_id' => $ml_data->id)
						// );
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
						return $data = ['provider' => $provider, 'ml'=> $ml_data];
					}, 1);
				} catch (Exception $e) {
					//dd($e);
				}
				return ['error'=> false, 'msj'=> 'El producto fue creado correctamente :D'];
            }
                
		private function predictCategoryML($title){
			$response = "";
			$client = new Client(); //GuzzleHttp\Client
			try {
				$result = $client->post('https://api.mercadolibre.com/sites/MLM/category_predictor/predict', [
					GuzzleHttp\RequestOptions::JSON => [['title' => $title]]
				]);
				$response = $result->getBody()->getContents();
				$response = json_decode($response, true);
		
				$response = $response[0]["path_from_root"][0]["id"];
			} catch (\Throwable $th) {
				//throw $th;
				
				$response = null;
			}
			return $response;
        }
        
		private function validateKeepaResponse($res){
			$validation = true;
			if (!array_key_exists('products', $res)) {
				return $validation = false;
			}
			$stats = $res['products'];
			//$price = $stats[0]['stats']['current'][0];
			if (!array_key_exists(0, $stats)) {
				return $validation = false;
			}
			$stats = $stats[0];
			if (!array_key_exists('stats', $stats)) {
				return $validation = false;
			}
			$stats = $stats['stats'];
			if (!array_key_exists('current', $stats)) {
				return $validation = false;
			}
			$stats = $stats['current'];
			if (!array_key_exists(0, $stats)) {
				return $validation = false;
			}
			
			$validation = $stats;
			return $validation;
		}

    }

?>
