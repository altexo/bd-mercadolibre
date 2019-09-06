<?php
namespace App\Http\AppServices;

class Filters 
{
    public function wordsToExclude($string){
        $exclude = false;
        $matches = [
            "Apple",
            "Bose"
        ];
        
        foreach ($matches as $m) {
            if(preg_match("/{$m}/i", $string)) {
                $exclude = true;
            // echo 'true on: '.$m." <br>";
            }
        }
        return $exclude;
    }
}




?>