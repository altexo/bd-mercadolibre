<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DateTime;
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
class productsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products price in database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $current_date =  new \DateTime();
        // $product = Products::join('provider', 'products.provider_id', '=', 'provider.id')->where('provider.asin', '')
        echo "Update command called"."<br>";
        $response_array = [];
        $errors = [];


        $products = Ml_data::select('ml_data.id as ml_data_id','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
            ->join('products','ml_data.id','=','products.ml_data_id')
            ->join('provider', 'products.provider_id', '=', 'provider.id')
            ->where('products.provider_id','!=',1)
            ->where('provider.asin','!=', "")
            //->take(2)
            ->get();
           // return print_r($products);
        if ($products != NULL) {

                foreach ($products as $product) {
                  //  re$product->asin;
                        $asin = $product->asin;
                        if ($asin == "") {
                            continue;
                        }
                       
                        $client = new Client([
                            'base_uri'=> 'https://api.scrapehero.com/amaz_mx/product-details/?asin='.$asin.'&apikey=59242154b4e89e0fd599213326a2d4f78dba436eba0c70b19e33fccb',
                            'http_errors' => false
                        ]); 

                        $response = $client->request('GET');

                        $response = $response->getBody()->getContents();
                        //Arreglo de producto mediante asin
                        $res = json_decode($response, true);
                        if (empty($res)) {
                            $this->updateProductStatus($product->provider_id);
                            array_push($errors, ['title'=>$product->title,'empty_res'=>$asin]);
                        }
                        if ($res == null) {
                            $this->updateProductStatus($product->provider_id);
                            array_push($errors, ['title'=>$product->title,'null_res'=>$asin]);
                            continue;
                        }
                        //$a=array("Volvo"=>"XC90","BMW"=>"X5");
                        if (array_key_exists("price",$res))
                        {
                          //echo "Key exists!";
                        }
                        else
                        {
                            $this->updateProductStatus($product->provider_id);
                            array_push($errors, ['title'=>$product->title, 'price_not_found'=>$asin]);
                            continue;
                        }
                        //verificamos el precio no venga en null
                        if ( ($res['price'] == null) || ($res['price'] == "") ) {
                            $this->updateProductStatus($product->provider_id);
                            array_push($errors, ['title'=>$product->title,'price_nullOrEmpty'=>$asin]);
                            continue;
                        }
                        //Transform price
                        $providerPrice = $res['price'];
                        //Quitamos el signo de moneda del precio
                        $providerPrice = substr($providerPrice, 1);
                        $providerPrice = str_replace(',', '', $providerPrice);
                        $providerPrice = intval($providerPrice);
                        //Convertims a peso y aumentamos el precio del producto
                        $sell_price = 1.60 * $providerPrice;
                        $sell_price = round($sell_price);
                        
                        $pictures_array = [];
                         foreach ($res['images'] as $img) {  
                            $img_format = substr($img, -3);
                            if ($img_format == "png") {
                                continue;
                            }
                            array_push($pictures_array, ['source' => $img]);
                         }    
                         //Codificamos el arreglo de imagenes a json 
                        $pictures_array = json_encode($pictures_array);
                        //Comienza transaccion de captura de nuevo producto 
                        try {
                            $transaction = DB::transaction(function() use($res, $providerPrice, $product, $sell_price, $pictures_array){
                                $ml_data = Ml_data::where('id',$product->ml_data_id)->first();
                                $ml_data->price = $sell_price;
                                $ml_data->available_quantity = 99;
                                $ml_data->save();

                                $provider = Provider::where('id',$product->provider_id)->first();
                                $provider->provider_link = $res['url'];
                                $provider->price = $providerPrice;
                                $provider->provider_status_id = 1;
                                $provider->save();

                                $pictures = Pictures::where('ml_data_id', $product->ml_data_id)->first();
                                $pictures->url = $pictures_array;
                                $pictures->save();
                                
                                return $transaction = ['provider'=> $provider, 'ml_data'=> $ml_data->title];

                            });
                        } catch (Exception $e) {
                            $response = $e;
                        }
                            array_push($response_array, $transaction);
                    }
                }
        //             //$response = $products;
        // return response()->json(['ok' => $response_array,'errors'=>$errors]);
               // print_r($response_array);
            return echo "Update products complete"."<br>";
        }

    private function updateProductStatus($id){
        $provider = Provider::where('id',$id)->first();
        $provider->provider_status_id = 2;
        $provider->save();
    }


}
