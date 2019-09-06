<?php

namespace App\Http\Controllers;
use App\Http\AppServices\MLToken;
use Illuminate\Http\Request;
use App\Http\AppServices\MessagesML;
use App\Http\AppServices\Meli;
use App\Http\AppServices\MLConnection;
class MessagesController extends Controller
{
    public function index(){

        $messagesML = new MessagesML();
        $msjs = $messagesML->getPendingMessages();
        return $msjs;
    }

    public function createTestUser(){
        $MLToken = new MLConnection();
        $conn = $MLToken->getConnection();
        $meli = new Meli($conn->appId, $conn->secretKey);
        $params = array('access_token' => $conn->token);
        $body = array('site_id' => 'MLM');
        $result = $meli->post('/users/test_user', $body, $params);
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
}
