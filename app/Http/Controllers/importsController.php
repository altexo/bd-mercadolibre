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

        public function importNesAsins(Request $request){
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

            $count = 0;
            $not_found = [];
            foreach ($rows as $row) {
            	$row = array_combine($header, $row);
            	// echo $row['Nombre']."<br>";
            	// echo $row['ASIN']."<br>";
            	// echo $row['Categoria']."<br>";
        		$asin = $row['ASIN'];
        		$title = $row['Nombre'];
        		$base_category = $row['Categoria'];
	    		if ($asin == "") {
	    			continue;
	    		}
	    			   
				$client = new Client([
					'base_uri'=> 'https://api.scrapehero.com/amaz_mx/product-details/?asin='.$asin.'&apikey=59242154b4e89e0fd599213326a2d4f78dba436eba0c70b19e33fccb',
				   	'http_errors' => false
				   	//	'headers' => ['X-Mashape-Key' => 'zg0snQgYOimshgrnP0Mx5m9O3vlQp1cjQX1jsncrcfBCh3zcps', 'Accept' => 'application/json']
				]); 
				$response = $client->request('GET');

				$response = $response->getBody()->getContents();
				//Arreglo de producto mediante asin
				$res = json_decode($response, true);
				if (empty($res)) {
					array_push($errors, ['title'=>$row['Nombre'],'empty_res'=>$asin]);
				}
				if ($res == null) {
					array_push($errors, ['title'=>$row['Nombre'],'null_res'=>$asin]);
					continue;
				}

				if (array_key_exists("price",$res)){
					//echo "Key exists!";
				}
				else {
					array_push($errors, ['title'=>$row['Nombre'], 'price_not_found'=>$asin]);
				  	continue;
				}
				//verificamos el precio no venga en null
				if ( ($res['price'] == null) || ($res['price'] == "") ) {
					array_push($errors, ['title'=>$row['Nombre'],'price_nullOrEmpty'=>$asin]);
					continue;
				}
				//Convertimos las imagenes en una lista de arreglos
				$pictures_array = [];
				 foreach ($res['images'] as $img) {        
		            array_push($pictures_array, ['source' => $img]);
		         }                           
        		//Codificamos el arreglo de imagenes a json 
        		$pictures_array = json_encode($pictures_array);
				//Transform price
				$providerPrice = $res['price'];
				//Quitamos el signo de moneda del precio
				$providerPrice = substr($providerPrice, 1);
				$providerPrice = str_replace(',', '', $providerPrice);
				$providerPrice = intval($providerPrice);
				//Convertims a peso y aumentamos el precio del producto
				$sell_price = 1.40 * $providerPrice;
				$sell_price = round($sell_price);
				//Comienza transaccion de captura de nuevo producto   		
				try {
					$data = DB::transaction(function () use($providerPrice, $sell_price,$res, $pictures_array, $asin, $title, $base_category) {
			    		//Create new provider object
						$provider = new Provider;
						$provider->provider_link = $res['url'];
						$provider->price = substr($res['price'],1);
						$provider->asin = $asin;
						$provider->provider_status_id = 1;
						$provider->save();

						//Create new ml_data object
						$ml_data = new Ml_data;
						$ml_data->category_id = $base_category;
						$ml_data->price = $sell_price;
						$ml_data->available_quantity = 99;
						$ml_data->currency_id ='MXN';
						$ml_data->buying_mode = 'buy_it_now';
						$ml_data->listing_type_id = 'gold_pro';
						$ml_data->description = $res['small_description'];
						$ml_data->accepts_mercadopago = 1;
						$ml_data->save();

						//Create new producs object
						$products = new Products;
						$products->title = $title;
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

						return $data = ['provider' => $provider, 'ml'=> $ml_data];
					}, 1);
				} catch (Exception $e) {
					//dd($e);
				}
                  
                 array_push($response_array, $data);   
                }
                echo '<pre>';
                print_r($response_array);
                echo "</pre>";
        }
}
