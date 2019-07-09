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
    public function updatePrice($asin, $price){
        
        $appId = ENV('APP_ID');
        $secretKey = ENV('SECRET_KEY');

        $meli = new Meli($appId, $secretKey);
        $token = $this->getToken();

        $params = array('sku' => $asin,'access_token' => $token);

        $result = $meli->get('/users/'.ENV('SELLER_ID').'/items/search', $params, true);


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