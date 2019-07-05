<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attributes;
use App\Http\AppServices\Meli;
use Auth;
use App\User;
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
        $user = Auth::user();
        
        echo "Token actual <br>";
        echo $user->ml_token."<br>";
        echo "================== <br>";
        echo "Intentando refrescar... <br> <br>";

        $appId = ENV('APP_ID');
        $secretKey = ENV('SECRET_KEY');
        $redirectURI = ENV('REDIRECT_URI');
        $siteId = ENV('SITE_ID');
         $meli = new Meli($appId, $secretKey, $user->ml_token, $user->r_token) ;
        $refresh = $meli->refreshAccessToken();
        echo "Token refrescado... <br>";
        echo "<pre>";
        print_r($refresh);
        echo "</pre>";
        // $u = User::find($user->id);

              
                // Now we create the sessions with the authenticated user
             

        // echo '<p><a alt="Login using MercadoLibre oAuth 2.0" class="btn" href="' . $meli->getAuthUrl($redirectURI, Meli::$AUTH_URL[$siteId]) . '">Authenticate</a></p>';
        // if (isset($_GET['code'])) {
        //     $user = $meli->authorize($_GET['code'], $redirectURI);
        //     echo $user['body']->access_token."<br>";
        //     echo time() + $user['body']->expires_in."<br>";
        //     echo $user['body']->refresh_token."<br>";
        // }
      

        
        //return view('mercadojs');
    }

 
}
