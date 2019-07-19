<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Ml_data;
use App\Pictures;
use App\Products;
use App\Attributes;
use App\Provider;
use App\Http\AppServices\GetProductKeepa;
class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Allow: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }
        $products = Ml_data::select('ml_data.id as ml_data_id','products.id as product_id', 'provider_status.id as status_id', 'provider_status.status_name','products.type_id' ,'products.margin_sale','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
    	->join('products','ml_data.id','=','products.ml_data_id')
		->join('provider', 'products.provider_id', '=', 'provider.id')
		->join('provider_status','provider.provider_status_id','=','provider_status.id')
    	->where('products.provider_id','!=',1)
        ->paginate(20);
        

		return response()->json($products);
        //return response()->json(['success'=> 'done men'], 500);
    }

    public function allProducts(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Allow: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }
        $products = Ml_data::select('ml_data.id as ml_data_id','products.id as product_id', 'provider_status.id as status_id', 'provider_status.status_name','products.type_id' ,'products.margin_sale','ml_data.updated_at','provider.id as provider_id','products.title', 'provider.asin', 'ml_data.price as ml_price', 'provider.price as provider_price')
    	->join('products','ml_data.id','=','products.ml_data_id')
		->join('provider', 'products.provider_id', '=', 'provider.id')
        ->join('provider_status','provider.provider_status_id','=','provider_status.id')
        ->get();
        

		return response()->json($products);
    }
    public function newProductview()
    {
        return view('products.addNew');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
       // return $request;
        $getProduct = new GetProductKeepa();
        $getProduct = $getProduct->scrapProduct($request->asin);
        if ($getProduct['error'] == true) {
            return redirect()->back()->with('error', $getProduct['msj']);
        }
        else {
            return redirect()->back()->with('success', $getProduct['msj']);
        }
       
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

        public function importProducts(Request $request){

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
           //dd($header);
            $count = 0;
            $not_found = [];
            foreach ($rows as $row) {

                    $row = array_combine($header, $row);
                    $title = utf8_encode($row['title']);
                    //$provider_link = utf8_encode($row['Link Provedor']);
                    $title = rtrim($title);
                    // $product = DB::table('products')
                    //     ->where('title', 'like', "%{$title}%")
                    //     ->first();
                    $product = Products::where('title', 'like', "%{$title}%")->first();
                    if ($product != NULL) {
                        $provider_id = DB::table('provider')->insertGetId([
                            //'provider_link' => $provider_link,
                            'asin' => utf8_encode($row['asin']),
                            //'shipping_price' => $row['Envio'],
                            //'price' => $row['Precio Provedor']
                        ]);

                        $product->provider_id = $provider_id;
                        $product->save();

                        $count++;
                            echo '<pre>';
                            echo $product->title."<br>";
                            echo '</pre>';
                            
                       }else{
                            array_push($not_found, $title);
                       }
                       
                  //  $provider = DB::table('provider')->where('')
                  
                    // $ml_data_id = DB::table('ml_data')->select('id')->where('ml_id', '=', $row['ml_id'])->get();

                  
                    
                }
                echo $count;
                echo "--Not Found--";
                echo '<pre>';
                print_r($not_found);
                echo '</pre>';
        }
    public function dummy(Request $r){
         return response()->json(['success'=> $r->obj], 500);
    }

    public static function convert_from_latin1_to_utf8_recursively($dat)
   {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

            return $dat;
        } else {
            return $dat;
        }
   }

    public function publishAll(){
        $response = "";
        $error = false;
        $response = Ml_data::with('pictures', 'shipping', 'tags', 'products','attributes')->where('description','!=', '"NULL"')->get();

        foreach ($response as $r) {
          $prod = $r->products;
          $asin = Provider::select('asin')->where('id', $prod[0]->provider_id)->first();
          $r->asin = $asin->asin;    
          
        }
        return response()->json(['error'=> $error,'response'=> $response]);
    }

    public function publishNew(){
        $response = "";
        $error = false;
      $response = DB::table('ml_data')
      ->select('ml_data.*','products.title', 'products.margin_sale','provider.id as provider_id','provider.provider_status_id','provider.asin','pictures.*','shipping.*','tags.*')
      ->join('products', 'ml_data.id','=','products.ml_data_id')
      ->join('pictures','pictures.ml_data_id','=','ml_data.id')
      ->join('shipping','shipping.ml_data_id','=','ml_data.id')
      ->join('tags','tags.ml_data_id','=','ml_data.id')
      ->join('provider', 'provider.id','=','products.provider_id')
      ->where('products.provider_id','!=',1)
      ->where('provider.provider_status_id','=',4)
      ->get();

        return response()->json(['error'=> $error,'response'=> $response]);
    }


    public function updateProductsPrices(){
      $response = "";
      $error = false;
      $response = DB::table('ml_data')
      ->select('ml_data.*','products.title', 'products.margin_sale','provider.provider_link','provider.asin', 'provider.provider_status_id', 'pictures.url')
      ->join('products', 'ml_data.id','=','products.ml_data_id')
      ->join('pictures','pictures.ml_data_id','=','ml_data.id')
      ->join('provider', 'provider.id','=','products.provider_id')
      ->where('products.provider_id','!=',1)
      ->where('provider.provider_status_id','=',1)
      ->Orwhere('provider.provider_status_id','=',2)
      ->get();
      $count = 0;
      foreach ($response as $r) {
        $count++;
      }
      return response()->json(['count'=> $count,'error'=>$error, 'response'=>$response]);
    }

    public function deleteProduct(Request $r){
        $response = true;
        $msj = "";
        try {
            Provider::destroy($r->provider_id);
            Ml_data::destroy($r->ml_id);
            $msj = "Se elimino correctamente";
        } catch (\Exception $e) {
            $msj = "Error al eliminiar".$e;
            $response = false;
        }
        
        return response()->json(['msj'=> $msj, 'response'=>$response]);
    }
    public function updateState(Request $request){
        $product = Provider::find($request->id);
        $state = $request->state;
        if ($product != null) {
            $product->provider_status_id = $state;
            $product->save();
            return ['error'=>false, 'msj' => 'success'];
        }
        return ['error'=>true, 'msj' => 'Not found'];
    }



}
