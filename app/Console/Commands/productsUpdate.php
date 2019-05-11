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
        $count = 0;
        echo "Update command called\n";
        $response_array = [];
        $errors = [];


        $products = Ml_data::select('ml_data.id as ml_data_id','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
            ->join('products','ml_data.id','=','products.ml_data_id')
            ->join('provider', 'products.provider_id', '=', 'provider.id')
            ->where('products.provider_id','!=',1)
            ->where('provider.asin','!=', "")
            ->get();

        if ($products != NULL) {
            
            foreach ($products as $product) {
                echo "Iniciando con : ".$product->asin."\n";
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
                if (!$res['products']) {
                    $this->updateProductStatus($product->provider_id);
                    continue;
                }
                $stats = $res['products'];
                $price = $stats[0]['stats']['current'][0];
                $priceThirdPartySeller = $stats[0]['stats']['current'][1];

                if ($price == -1) {
                    if ($priceThirdPartySeller == -1) {
                        $this->updateProductStatus($product->provider_id);
                        array_push($errors, ['title'=>$product->title,'No disponible en stock'=>$asin]);
                        echo $asin." No disponible en stock \n";
                        // sleep(65);
                        continue;
                    }else{
                        $price = $priceThirdPartySeller;
                    }
                   
                }
               
                $decimalPrice = sprintf('%.2f', $price / 100);
                $providerPrice = $decimalPrice;
                $sell_price = 1.60 * $providerPrice;
                $sell_price = round($sell_price);
               

                try {
                    $transaction = DB::transaction(function() use($providerPrice, $product, $sell_price){
                        $ml_data = Ml_data::where('id',$product->ml_data_id)->first();
                        $ml_data->price = $sell_price;
                        $ml_data->available_quantity = 99;
                        $ml_data->save();

                        $provider = Provider::where('id',$product->provider_id)->first();
                        $provider->price = $providerPrice;
                        $provider->provider_status_id = 1;
                        $provider->save();

                    });
                } catch (Exception $e) {
                    $response = $e;
                }
                    echo 'ACTUALIZADO ID: '.$transaction.' ASIN: '.$asin."\n";
                    array_push($response_array, $transaction);
                    $count++;
                    sleep(65);
            }
            
        }
            echo "Se completo la actualizacion de productos\n Productos Actualizados: ".$count;
            return "Done";
    }

    private function updateProductStatus($id){
        $provider = Provider::where('id',$id)->first();
        $provider->provider_status_id = 2;
        $provider->save();
    }


}
