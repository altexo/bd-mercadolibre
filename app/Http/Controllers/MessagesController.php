<?php

namespace App\Http\Controllers;
use App\Http\AppServices\MLToken;
use Illuminate\Http\Request;
use App\Http\AppServices\MessagesML;
use App\Http\AppServices\MLConnection;
class MessagesController extends Controller
{
    public function index(){
        $messagesML = new MessagesML();
        $msjs = $messagesML->getMessagesList();
        return $msjs;
    }

    public function createTestUser(){
        $MLToken = new MLConnection();
        $conn = $MLToken->getConnection();
        $meli = new Meli($conn->appId, $conn->secretKey);
        $params = array('access_token' => $conn->token);
        $result = $meli->post('/test_user', $params, true);
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
}
