<?php

namespace App\Http\AppServices;
use GuzzleHttp\Client;
use GuzzleHttp;
use DB;
use App\User;

class UpdateInML{
    
    private function getToken(){
        $token = User::find(11);
        return $token->ml_token;
    }
    public function updatePrice($asin, $price, $status){
        
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
                $body = ['price' => $price, 'status' => $status];
                $result = $meli->put('/items/'.$ml_id, $body,$params);
                return $result;
            } catch (\Throwable $th) {
                throw $th;
            }
        } catch (\Throwable $th) {
            throw $th;
            echo "Error retriving From ML, SKU: ".$asin."\n";
        }
        

       


        return $result;

    //     var ml_id = data[2].results;
    //     if(ml_id && ml_id.length) {
    //         console.log(ml_id)
    //         console.log(asin+' '+price)
    //         MELI.put('/items/'+ml_id[0],{'price': price, 'status':status}, function(data){
    //             console.log(data);

    //         });
    //       //  MELI.put()
    //     } else {
    //         console.log('empty')
    //     }
       
      
    // });

    }



}

?>