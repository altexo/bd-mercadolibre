<?php

namespace App\Http\Controllers;
use Goutte\Client;
use Illuminate\Http\Request;

class ScrapperController extends Controller
{
    public function index (Client $client){
    	// Go to the symfony.com website
		$crawler = $client->request('GET', 'https://www.amazon.com/dp/B00IE6Y3SC');
		//$inlineIds = 'id=priceblock_snsprice_Based';
		try{
			$asin = $crawler->filter('span#priceblock_ourprice.asize-medium.a-color-price')->text();
		} catch(Exception $e) { // I guess its InvalidArgumentException in this case
   			dd($e);
		}
		dd($asin->html());
    }
}

         
     




    

    
    
    
        
        
        
        
    	    
                






    
    










    
    
     
         
