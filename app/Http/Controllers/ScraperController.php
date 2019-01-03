<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Ml_data;
use App\Provider;
use App\Products;
use App\Tags;
use App\Pictures;
use App\Shipping;
use DB;

class ScraperController extends Controller
{
    public function index(){
    	$client = new Client([
    		'base_uri'=> 'https://scrapehero-amazon-product-info-v1.p.mashape.com/product-details?asin=B010TQY7A8', 
    		'headers' => ['X-Mashape-Key' => 'zg0snQgYOimshgrnP0Mx5m9O3vlQp1cjQX1jsncrcfBCh3zcps', 'Accept' => 'application/json']]); 
		$response = $client->request('GET');
		//$body = $client->getBody();
		//$response = new R;
		$response = $response->getBody()->getContents();
		$res = json_decode($response, true);//Arreglo de producto mediante asin

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
		//Convertims a peso y aumentamos el precio del producto
		$providerPrice = 1.60*($providerPrice*20);
		
		//Comienza transaccion de captura de nuevo producto 
		try {
			DB::transaction(function () use($providerPrice, $res, $pictures_array) {
	    		//Create new provider object
				$provider = new Provider;
				$provider->provider_link = $res['url'];
				$provider->price = substr($res['price'],1);
				$provider->asin = $res['product_information']['ASIN'];
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
				$tags = new Tags;
				$tags->tags_object = '["brand_verified","immediate_payment","cart_eligible"]';
				$tags->ml_data_id = $ml_data->id;
				$tags->save();

				//return $commited = ['provider' => $provider, 'ml'=> $ml_data];
			}, 2);
		} catch (Exception $e) {
			dd($e);
		}
		

    	return ['Succeed'];
    }


}
