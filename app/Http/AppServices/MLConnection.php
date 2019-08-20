<?php
namespace App\Http\AppServices;

class MLConnection
{
  
    public function getConnection(){

        $token = User::find(12);
        $appId = ENV('APP_ID');
        $secretKey = ENV('SECRET_KEY');

        $obj = array('token' => $token,'appId'=>  $appId, 'secretKey' => $secretKey);

        $connection = (object) $obj;
        return $connection;
    }


}
