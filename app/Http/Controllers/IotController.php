<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class IotController extends Controller
{
     public function storeData(Request $request){
    	$data = DB::table('iot_table')->insert(
		    ['data' => $request]
		);
    	return response()->json([$data]);
    }
}
