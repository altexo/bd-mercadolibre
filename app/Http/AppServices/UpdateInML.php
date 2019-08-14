<?php

namespace App\Http\AppServices;
use GuzzleHttp\Client;
use GuzzleHttp;
use DB;
use App\User;

class UpdateInML{
    
    private function getToken(){
        $token = User::find(12);
        return $token->ml_token;
    }

    public function updatePrice($asin, $price, $status, $description){
        
        $appId = ENV('APP_ID');
        $secretKey = ENV('SECRET_KEY');

        $meli = new Meli($appId, $secretKey);
        $token = $this->getToken();

        $params = array('sku' => $asin,'access_token' => $token);
        $price = 1.60 * $price;
        $price = round($price);

        try {
            $result = $meli->get('/users/'.ENV('SELLER_ID').'/items/search', $params, true);
            $ml_id = $result['body']['results'][0];
            try {
                $params = ['access_token' => $token ];
                $body = ['price' => $price, 'description' => $description,'status' => $status];
                $result = $meli->put('/items/'.$ml_id, $body,$params);
                if ($result['httpCode'] == 200) {
                    $params = ['access_token' => $token ];
                    $body = ['plain_text' => $description];
                    $result = $meli->put('/items/'.$ml_id.'/description', $body,$params);
                    return true;
                }else{
                    return json_decode(json_encode($result), true);
                }
                
            } catch (\Exception $th) {
                return $th;
            }
        } catch (\Exception $th) {
            return $th;
            //echo "Error retriving From ML, SKU: ".$asin."\n";
        }

    }

    public function disableProduct($asin){
        
        $appId = ENV('APP_ID');
        $secretKey = ENV('SECRET_KEY');

        $meli = new Meli($appId, $secretKey);
        $token = $this->getToken();

        $params = array('sku' => $asin,'access_token' => $token);

        try {
            $result = $meli->get('/users/'.ENV('SELLER_ID').'/items/search', $params, true);
            $ml_id = $result['body']['results'][0];
            try {
                $params = ['access_token' => $token ];
                $body = ['status' => 'paused'];
                $result = $meli->put('/items/'.$ml_id, $body,$params);
                if ($result['httpCode'] == 200) {
                    return true;
                }else{
                    return json_decode(json_encode($result), true);
                }
                
            } catch (\Exception $th) {
                return $th;
            }
        } catch (\Exception $th) {
            return $th;
            //echo "Error retriving From ML, SKU: ".$asin."\n";
        }

    }



}

?>