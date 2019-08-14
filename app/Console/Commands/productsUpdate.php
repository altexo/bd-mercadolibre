<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Ml_data;
use App\Provider;
use DB;
use Mail;
use App\Http\AppServices\UpdateInML;
use App\Mail\ProductsUpdatesNotification;
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
        //$ml_updated = 0;

        $date = date('Y-m-d H:i:s');
        echo "Update command called\n";
        echo "Fecha y hora de ejecucion: ".$date."\n";
        $response_array = [];
        $errors = [];


        $products = Ml_data::select('ml_data.id as ml_data_id','ml_data.updated_at', 'ml_data.description','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
            ->join('products','ml_data.id','=','products.ml_data_id')
            ->join('provider', 'products.provider_id', '=', 'provider.id')
            ->where('products.provider_id','!=',1)
            ->where('provider.asin','!=', "")
            ->get();

        if ($products != NULL) {
            
            foreach ($products as $product) {
                echo "Iniciando con : ".$product->asin."\n";
                $description = "IMPORTATENTE ANTES DE OFERTAR pregunta por disponibilidad (si el producto es en varios colores o tallas es MUY importante que nos preguntes antes de ofertar), nosotros te lo mandamos directo a tu domicilio pregunta como puedes conseguir ENVÍO GRATIS o los costos de envío. Una vez echa la compra te pediremos tus datos de envío por mensaje privado de Mercado Libre, para mandarte tu producto lo antes posible! \n\n\n".$product->description;
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
          
                $validation = $this->validateKeepaResponse($res);
                if ($validation == false) {
                    echo 'Error al consultar en Keepa!: '.$asin."\n";
                }
                $variants = $res['products'][0];
               
                $stats = $validation;
                $price = $stats[0];
                $priceThirdPartySeller = $stats[1];

                if ($price == -1) {
                    if ($priceThirdPartySeller == -1) {
                        $this->updateProductStatus($product->provider_id);
                        $this->disableInML($asin);
                        array_push($errors, ['title'=>$product->title,'No disponible en stock'=>$asin]);
                        echo $asin." No disponible en stock \n";
                         sleep(3);
                        continue;
                    }else{
                        $price = $priceThirdPartySeller;
                    }
                   
                }
                if ($price < 100) {
                    $this->updateProductStatus($product->provider_id);
                    $this->disableInML($asin);
                    array_push($errors, ['title'=>$product->title,'Precio demasiado bajo (Menor a 100 MXN)'=>$asin]);
                    echo $asin." Precio demasiado bajo (Menor a 100 MXN): ".$price." \n";
                     sleep(3);
                    continue;
                }
                
                if ($variants['variationCSV'] != null) {
                    $this->updateProductStatus($product->provider_id);
                    $this->disableInML($asin);
                    array_push($errors, ['title'=>$product->title,'Este producto contiene variantes'=>$asin]);
                    echo $asin." Este producto contiene variantes \n";
                     sleep(3);
                    continue;
                }
               
                $decimalPrice = sprintf('%.2f', $price / 100);
                $providerPrice = $decimalPrice;
                // $sell_price = 1.60 * $providerPrice;
                $sell_price = round($providerPrice);
               

                try {
                    $transaction = DB::transaction(function() use($providerPrice, $product, $sell_price, $description){
                        $ml_data = Ml_data::where('id',$product->ml_data_id)->first();
                        $ml_data->price = $sell_price;
                        $ml_data->available_quantity = 99;
                        $ml_data->description = $description;
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
                    echo "Iniciando actualización en Mercadolibre.. \n";
                    $updateInMl = new UpdateInML();
                    $updateInMl = $updateInMl->updatePrice($asin, $sell_price, 'active' ,$description);
                    if ($updateInMl->status == true) {
                        print_r($updateInMl);
                        echo "OK \n";
                    }else{
                        echo "No actualizado en ML \n";
                    }
                    array_push($response_array, $transaction);
                    $count++;
                   
                    sleep(10);
            }
            
        }
            $msj = "El precio de los productos fueron actualizados correctamente, Total actualizados: ".$count."\n";

            Mail::to('emmanuel_hernandez@live.com.mx')->send(new ProductsUpdatesNotification($msj));
            echo "Se completo la actualizacion de productos\n Productos Actualizados: ".$count."\n";
            echo "Hora: ".date('Y-m-d H:i:s')."\n";
            return "Done";
    }

    private function updateProductStatus($id){
        $provider = Provider::where('id',$id)->first();
        $provider->provider_status_id = 2;
        $provider->save();
    }

    private function disableInML($asin){
        $updateInMl = new UpdateInML();
        $updateInMl->disableProduct($asin);
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
