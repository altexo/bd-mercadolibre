<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Provider;
class importsController extends Controller
{
    public function importAsinUpdate(Request $request){

            $validator = \Validator::make($request->all(), [
                'file' => 'required',
            ]);

            if ($validator->fails()) {
                echo "Fail :(";
                //return redirect()->back()->withErrors($validator);
            }
            
            $file = $request->file;
            $csvData = file_get_contents($file);
            //dd($csvData);
            $rows = array_map("str_getcsv", explode("\r\n", $csvData));
            $header = array_shift($rows);
            //return dd($header);
           //return dd($rows);
          //  $rows = array_map("utf8_encode", $rows );

            $count = 0;
            $not_found = [];
            foreach ($rows as $row) {
            	
            	 // if ($row < 3) {
           			// echo "yes";
            	 // }
            				 $row = array_combine($header, $row);
            		//echo $row['asin'];
                    
                    
                    $provider = Provider::where('id', '=', $row['asin'])->first();
                    if ($provider != NULL) {

                        $provider->asin = $row['asin'];
                        $provider->save();

                        $count++;
                            echo '<pre>';
                            echo $provider->asin.' '.$row['title']."<br>";
                            echo '</pre>';
                            
                       }else{
                            array_push($not_found, $row);
                       }
                       
          
                  
                    
                }
                echo $count;
                echo "--Not Found--";
                echo '<pre>';
                print_r($not_found);
                echo '</pre>';
        }
}
