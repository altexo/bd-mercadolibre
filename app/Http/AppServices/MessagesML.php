<?php
namespace App\Http\AppServices;

use App\Http\AppServices\MLToken;
class MessagesML
{
    private function getMLConnection(){
        $MLToken = new MLConnection();
        $conn = $MLToken->getConnection();
        return $conn;
    }

    public function getMessagesList(){
        $mlConn = $this->getMLConnection();
        //return $mlConn->token;
        $meli = new Meli($mlConn->appId, $mlConn->secretKey);
        $params = array('access_token' => $mlConn->token);
        $body = array('site_id' => 'MLM');
        $result = $meli->get('/messages/pending_read', $body, $params);
        return $result;


    }
    public function getMessageById(){

    }
    public function resoibseMessage($id){

    }
}


?>