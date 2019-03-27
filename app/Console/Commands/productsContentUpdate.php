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
use App\Pictures;
use DB;
use Date;

class productsContentUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:updateWithContent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    
        echo "Update command called\n";
        $response_array = [];
        $errors = [];


        $products = Ml_data::select('ml_data.id as ml_data_id','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
            ->join('products','ml_data.id','=','products.ml_data_id')
            ->join('provider', 'products.provider_id', '=', 'provider.id')
            ->where('products.provider_id','!=',1)
            ->where('provider.asin','!=', "")
            ->take(2)
            ->get();
            
            // $count = count($products);
            // return response()->json($count);
        if ($products != NULL) {

            foreach ($products as $product) {
                $asin = $product->asin;
                if ($asin == "") {
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
                $stats = $res['products'];
                $price = $stats[0]['stats']['current'][0];

                if ($price == -1) {
                    $this->updateProductStatus($product->provider_id);
                    array_push($errors, ['title'=>$product->title,'No disponible en stock'=>$asin]);
                    echo $asin." No disponible en stock \n";
                   // sleep(65);
                    continue;
                }
                $imgs = $res['products'][0];
                $title = $imgs['title'];
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
                $sell_price = 1.60 * $providerPrice;
                $sell_price = round($sell_price);
                $pictures_array = json_encode($pictures_array);

                try {
                    $transaction = DB::transaction(function() use($providerPrice, $product, $sell_price, $pictures_array, $title){
                        $ml_data = Ml_data::where('id',$product->ml_data_id)->first();
                        $ml_data->price = $sell_price;
                        $ml_data->available_quantity = 99;
                        $ml_data->save();

                        $provider = Provider::where('id',$product->provider_id)->first();
                        $provider->price = $providerPrice;
                        $provider->provider_status_id = 1;
                        $provider->save();

                        $products = Products::where('id', $product->ml_data_id)->first();
                        $products->title = $title;
                        $products->save();

                        $pictures = Pictures::where('ml_data_id', $product->ml_data_id)->first();
					    $pictures->url = $pictures_array;
						$pictures->save();
                        return $transaction = $ml_data->id;//['provider'=> $provider, 'ml_data'=> $ml_data->id];

                    });
                } catch (Exception $e) {
                    $response = $e;
                }
                    echo 'ACTUALIZADO ID: '.$transaction.' ASIN: '.$asin."\n";
                    array_push($response_array, $transaction);
                    sleep(65);
            }
            
        }
            echo "Update products complete\n";
            return "Done";
        }

    private function updateProductStatus($id){
        $provider = Provider::where('id',$id)->first();
        $provider->provider_status_id = 2;
        $provider->save();
    }
}
