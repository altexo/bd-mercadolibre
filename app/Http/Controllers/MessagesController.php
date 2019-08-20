<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\MessagesML;
class MessagesController extends Controller
{
    public function index(){
        $messagesML = new MessagesML();
        $msjs = $messagesML->getMessagesList();
        return $msjs;
    }
}
