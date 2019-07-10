<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attributes;
use App\Http\AppServices\Meli;
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

         
        // $appId = ENV('APP_ID');
        // $secretKey = ENV('SECRET_KEY');
        // $siteId = ENV('SITE_ID');
        // $redirectURI = ENV('REDIRECT_URI');

        // $meli = new Meli($appId, $secretKey);

        // echo '<p><a alt="Login using MercadoLibre oAuth 2.0" class="btn" href="' . $meli->getAuthUrl($redirectURI, Meli::$AUTH_URL[$siteId]) . '">Authenticate</a></p>';
        // if (isset($_GET['code'])) {
        //     $user = $meli->authorize($_GET['code'], $redirectURI);
        //     echo $user['body']->access_token."<br>";
        //     echo time() + $user['body']->expires_in."<br>";
        //     echo $user['body']->refresh_token."<br>";
        // }
        return view('mercadojs');
    }

 
}
