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

        $result = $meli->get('/messages/pending_read', $mlConn->token, true);
        return $result;


    }
    public function getMessageById(){

    }
    public function resoibseMessage($id){

    }
}


?>