<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attributes;
use App\Http\AppServices\Meli;
use Auth;
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
        // $user = Auth::user();
        
        // echo "Token actual <br>";
        // echo $user->ml_token;
        // echo "================== <br>";
        // echo "Intentando refrescar... <br>";

        // $appId = ENV('APP_ID');
        // $secretKey = ENV('SECRET_KEY');
        // $meli = new Meli($appId, $secretKey, $user->ml_token) ;
        // $refresh = $meli->refreshAccessToken();

        // echo "<pre>";
        // print_r($refresh);
        // echo "</pre>";

        return view('mercadojs');
    }

 
}
