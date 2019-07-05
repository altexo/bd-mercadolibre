<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp;
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
      

	public function getView(){
		return view('get-products-by-seller');
	}
		public function getProductsBySeller(Request $r){
			$client = new Client([
				'base_uri' => 'https://api.keepa.com/seller?key='.ENV('KEEPA_TOKEN').'&domain=11&seller='.$r->seller.'&update=48&storefront=1',
				'http_errors' => false
			   
			]);
			$response = $client->request('GET');

			$response = $response->getBody()->getContents();
			//Arreglo de producto mediante asin
			$res = json_decode($response, true);
			if (!array_key_exists('sellers', $res)) {
				echo "Sin tokens.. Esperando refil"."<br>";
				view('get-products-by-seller')->with('message','Sin tokens necesarios.. Esperande refil. Tokens actuales'.$res['tokensLeft']);
				
				
			}
			$asinList = $res['sellers'][$r->seller]['asinList'];
			echo "Cantidad de asins: ".count($asinList);
			echo "<br>";
			$this->parseProducts($asinList);

		}
		
		private function parseProducts($asins_array){
			echo "<table>";
				echo "<tr>";
				echo "<th>Asin</th>";
				echo "<th>Titulo</th>";
				echo "<th>Categoria</th>";
				echo "</tr>";
			$n = 0;	
			foreach ($asins_array as $asin) {
				$n++;
				$errors = [];
				$response_array = [];

	    		if ($asin == "") {
					
					echo "<td>".$asin."</td><td>No se encontro asin..</td>";
	    			continue;
				}
				
				$provider = Provider::where('asin', $asin)->first();
				if ($provider != null) {
					echo "Este asin ya existe: ".$asin."<br>";
					continue;
				}
	    			   
				$client = new Client([
                    'base_uri' => 'https://api.keepa.com/product?key='.ENV('KEEPA_TOKEN').'&domain=11&asin='.$asin.'&stats=24&history=0',
                    'http_errors' => false
                   
                ]); 
                $response = $client->request('GET');

                $response = $response->getBody()->getContents();
                //Arreglo de producto mediante asin
                $res = json_decode($response, true);
				$validation = $this->validateKeepaResponse($res);
				if ($validation == false) {
					echo "Error al obtener producto de Keppa: ".$asin."<br>";
					continue;
				}
				$stats = $validation;
				$price = $stats[0];
                $priceThirdPartySeller = $stats[1];

                if ($price == -1) {
                    if ($priceThirdPartySeller == -1) {
                        // array_push($errors, ['title'=>$product->title,'No disponible en stock'=>$asin]);
						//echo $asin." No disponible en stock "."<br>";
						echo "<td>".$asin."</td><td>No disponible</td>";
                        // sleep(65);
                        continue;
                    }else{
                        $price = $priceThirdPartySeller;
                    }
                   
                }
			   	$title = $res['products'][0]['title'];
			   	$title = substr($title,0,60);
				$base_category = $this->predictCategoryML($title);
				echo "<td>".$base_category."</td>";
				$imgs = $res['products'][0];
				$descripcion = $stats[0]['description'];
               // $title = $imgs['title'];
                $imgs = $imgs['imagesCSV'];
                $imgs = explode(",", $imgs);
                $pictures_array = [];
                foreach ($imgs as $img) {     
                    $image = 'https://images-na.ssl-images-amazon.com/images/I/'.$img;
                    array_push($pictures_array, ['source' => $image]);
                 }  
                //Transform price
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
				
				
				echo "<tr>";
				echo "<td>".$asin."</td><td>".$title."</td>";
			
				 array_push($response_array, $data);   
				 sleep(3);
				}
				echo "</table><br>";
				echo "Total de asins obtenidos: ".$n;
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
    	private function updateProductStatus($id){
    		$provider = Provider::where('id',$id)->first();
			$provider->provider_status_id = 2;
			$provider->save();

    	}
		}
		
		