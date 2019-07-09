<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attributes;
use App\Http\AppServices;
use Auth;
use App\User;
use App\Http\AppServices\GetProductKeepa;
use App\Http\AppServices\UpdateInML;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      


              
                // Now we create the sessions with the authenticated user
             

        // echo '<p><a alt="Login using MercadoLibre oAuth 2.0" class="btn" href="' . $meli->getAuthUrl($redirectURI, Meli::$AUTH_URL[$siteId]) . '">Authenticate</a></p>';
        // if (isset($_GET['code'])) {
        //     $user = $meli->authorize($_GET['code'], $redirectURI);
        //     echo $user['body']->access_token."<br>";
        //     echo time() + $user['body']->expires_in."<br>";
        //     echo $user['body']->refresh_token."<br>";
        // }
        $getProduct = new UpdateInML();
        $getProduct = $getProduct-> disableProduct("B000KBNTM0");

        echo "<pre>";
        print_r($getProduct);
        echo "</pre>";
        
        
      //  return view('mercadojs');
    }

 
}
