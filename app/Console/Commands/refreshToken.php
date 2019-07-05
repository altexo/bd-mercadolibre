<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\AppServices\Meli;
use App\User;

class refreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "hi \n";
        $user = User::find(11);
        
        echo "Token actual \n";
        echo $user->ml_token."\n";
        echo "================== \n";
        echo "Intentando refrescar... \n\n";

        $appId = ENV('APP_ID');
        $secretKey = ENV('SECRET_KEY');
         $meli = new Meli($appId, $secretKey, $user->ml_token, $user->r_token) ;
        $refresh = $meli->refreshAccessToken();
        echo "Token refrescado... \n";
        echo "Nuevo token: ".$refresh['body']->access_token."\n";
        echo "Nuevo r_token".$refresh['body']->refresh_token."\n";
        //$u = User::find(11);
        $user->ml_token = $refresh['body']->access_token;
        $user->r_token = $refresh['body']->refresh_token;
        $user->save();
      
       
    }
}
