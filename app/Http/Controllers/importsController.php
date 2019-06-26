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
class importsController extends Controller
{
    public function importAsinUpdate(Request $request){

            $validator = \Validator::make($request->all(), [
                'file' => 'required',
            ]);

            if ($validator->fails()) {
                echo "Fail :(";
                //return redirect()->back()->withErrors($validator);
            }
            
            $file = $request->file;
            $csvData = file_get_contents($file);
            $rows = array_map("str_getcsv", explode("\r\n", $csvData));
            $header = array_shift($rows);
            $count = 0;
            $not_found = [];
            foreach ($rows as $row) {
            				 $row = array_combine($header, $row);
            	
   					if ($row['asin'] == 'R') {
   						$provider = Provider::where('id', '=', $row['provider_id'])->first();
                    	$provider->state = 0;
                    	$provider->save();
                    	array_push($not_found, $row);
                    	continue;
                    }
                    $provider = Provider::where('asin', '=', $row['asin'])->first();
                  
                    if ($provider != NULL) {

                        $provider->asin = $row['asin'];
                        $provider->save();

                        $count++;
                            echo '<pre>';
                            echo $provider->asin.' '.$row['title']."<br>";
                            echo '</pre>';
                            
                       }else{
                            array_push($not_found, $row);
                       }
                       
          
                  
                    
                }
                echo $count;
                echo "--Not Found--";
                echo '<pre>';
                print_r($not_found);
                echo '</pre>';
        }

        public function importNewAsins(Request $request){
        	$errors = [];
        	$response_array = [];
        	        $validator = \Validator::make($request->all(), [
                'file' => 'required',
            ]);

            if ($validator->fails()) {
                echo "Fail :(";
                //return redirect()->back()->withErrors($validator);
            }
			
            $file = $request->file;
            $csvData = file_get_contents($file);
            $rows = array_map("str_getcsv", explode("\r\n", $csvData));
            $header = array_shift($rows);
			//Test 
			$rows = array_filter(array_map('array_filter', $rows));
		
			// break;
            $count = 0;
            $not_found = [];
            // dump($rows);
            
          
            foreach ($rows as $row) {
             
                $row = array_combine($header, $row);
                
        		$asin = $row['asin'];
        		$title = utf8_encode($row['titulo']);
                $base_category = $row['categoria'];
                $margin_sale = 1.40;//$row['margen'];
	    		if ($asin == "") {
	    			continue;
                }
                // if ($margin_sale == "") {
                //     $margin_sale = null;
                // }
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
                if (!array_key_exists('products', $res)) {
                    echo "Producto no encontrado: ".$asin."<br>";
                    continue;
                }
                $stats = $res['products'];
                $price = $stats[0]['stats']['current'][0];
                $priceThirdPartySeller = $stats[0]['stats']['current'][1];

                if ($price == -1) {
                    if ($priceThirdPartySeller == -1) {
                        // array_push($errors, ['title'=>$product->title,'No disponible en stock'=>$asin]);
                        echo $asin." No disponible en stock "."<br>";
                        // sleep(65);
                        continue;
                    }else{
                        $price = $priceThirdPartySeller;
                    }
                   
                }
               
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
                //$sell_price = 1.40 * $providerPrice;
                $sell_price = round($providerPrice);
                $pictures_array = json_encode($pictures_array);
				
						
				try {
					$data = DB::transaction(function () use($margin_sale, $providerPrice, $sell_price, $pictures_array, $asin, $title, $base_category, $descripcion) {
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
                        $products->margin_sale = $margin_sale;
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

						return $data = ['provider' => $provider, 'ml'=> $ml_data];
					}, 1);
				} catch (Exception $e) {
					//dd($e);
				}
				echo "Se creo producto: ".$title." con asin: ".$asin."<br>";
                 array_push($response_array, $data);   
				}
			
			    echo "<script type='text/javascript'>";
                echo "alert('Se termino de importar productos, haz clic en aceptar para ser redireccionado.')";
                echo "window.location.href =".route('products.view');
                echo "</script>";
                //return redirect()->route('products.view');
                // echo '<pre>';
                // print_r($response_array);
                // echo "</pre>";
        }
}
