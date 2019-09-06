<?php
namespace App\Http\AppServices;
use Illuminate\Http\Request;
use App\Http\AppServices\MLToken;
class MessagesML
{
    private function getMLConnection(){
        $MLToken = new MLConnection();
        $conn = $MLToken->getConnection();
        return $conn;
    }

    public function getMessagesList(Request $request){
        // $mlConn = $this->getMLConnection();
        // //return $mlConn->token;
        // $meli = new Meli($mlConn->appId, $mlConn->secretKey);
        // $params = array('access_token' => $mlConn->token);
        // $result = $meli->get('/messages/pending_read', $params, true);
        // $result = $result->body;
        // $result = $result->results;
        // if (in_array("count", $result)) 
        // { 
        //     session(['messages', $result["count"]]);
        // } 
       
        // // Retrieve a piece of data from the session...
        // $value = session('messages');

        // return $result;


    }
    public function getPendingMessages(){
        $mlConn = $this->getMLConnection();
        //return $mlConn->token;
        $meli = new Meli($mlConn->appId, $mlConn->secretKey);
        $params = array('access_token' => $mlConn->token);
        $result = $meli->get('/messages/pending_read', $params, true);
        $result = $result->body;
        $result = $result->results;
        if (in_array("count", $result)) 
        { 
            session(['messages', $result["count"]]);
        } 
       
    }
    public function getAllMessages(){
        $mlConn = $this->getMLConnection();
        //return $mlConn->token;
        $meli = new Meli($mlConn->appId, $mlConn->secretKey);
        $params = array('access_token' => $mlConn->token);
        $result = $meli->get('/messages/pending_read', $params, true);
        return $result;
    }
    public function getMessageById(){

    }
    public function resoibseMessage($id){

    }
}


?>