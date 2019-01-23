<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Ml_data;
use App\Pictures;
use App\Products;
use App\Attributes;
use App\Provider;
class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(['success'=> 'done men'], 500);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
         
    // if (!$request->category_id) {
    //     return response()->json(['fail'=> 'Ok', 'posted' => $request], 500);
    // }
        $response = '';
        $r = $this->convert_from_latin1_to_utf8_recursively($request);
        $atts = $r->request;
     //return response()->json(['fail'=> 'Ok', 'posted' => $atts->attributes], 500);
    $atts = $r->request;
    $json_attributes = json_encode($atts->attributes);
     $ml_data = Ml_data::where('ml_id',$r->ml_id)->first();
        if ($ml_data != NULL) {
            $ml_attributes = Attributes::where('ml_data_id', $ml_data->id)->first();
            $ml_attributes->attributes_details = $json_attributes;
            $ml_attributes->save();
            $response = "Updated";
        }
        else{    
            try{
                DB::transaction(function () use($r, $json_attributes) {
                  //  $atts = $r->request;

                    $ml_data_id = DB::table('ml_data')->insertGetId(
                        [   'ml_id' => $r->ml_id,
                            'category_id' => $r->category_id, 
                            'price' => $r->price,
                            'currency_id' => $r->currency_id,
                            'available_quantity' => $r->available_quantity,
                            'buying_mode' => $r->buying_mode,
                            'listing_type_id' => $r->listing_type_id,
                            'description' => $r->description['plain_text'],
                            'accepts_mercadopago' => $r->accepts_mercadopago,
                        ]
                    );

                    $json_pictures = json_encode($r->pictures);

                    DB::table('pictures')->insert(['ml_data_id' => $ml_data_id, 'url'=> $json_pictures]);
                    $json_shipping = json_encode($r->shipping);
                    $shipping_id = DB::table('shipping')->insertGetId(
                        [

                            'ml_data_id' => $ml_data_id,
                            'full_atts' => $json_shipping
                        ]
                    );


            
                    
                  
                            DB::table('attributes')->insert(
                                [
                                    'attributes_details' => $json_attributes,
                                    'ml_data_id' => $ml_data_id,
                                ]
                        );

                    $json_tags = json_encode($r->tags);
                    DB::table('tags')->insert([
                        'tags_object' => $json_tags,
                        'ml_data_id' => $ml_data_id
                    ]);
                    
                    DB::table('products')->insert([
                        'title' => $r->title,
                        'type_id' => 1,
                        'ml_data_id' => $ml_data_id,
                        'provider_id' => 1
                    ]);
                    $response = 'Created';
                });
            }catch( PDOException $e ){
                return response()->json(['err PDO'=> $e],500);
            }catch (\Exception $e){
                return response()->json(['err '=> new \Illuminate\Support\MessageBag(['catch_exception'=>$e])],500);
            }
        }

        return response()->json(['success'=> $response], 500);
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
      ->select('ml_data.*','products.title','provider.provider_status_id','pictures.*','shipping.*','tags.*')
      ->join('products', 'ml_data.id','=','products.ml_data_id')
      ->join('pictures','pictures.ml_data_id','=','ml_data.id')
      ->join('shipping','shipping.ml_data_id','=','ml_data.id')
      ->join('tags','tags.ml_data_id','=','ml_data.id')
      //->join('attributes','attributes.ml_data_id','=','ml_data.id')
      ->join('provider', 'provider.id','=','products.provider_id')
      ->where('products.provider_id','!=',1)
      ->where('provider.provider_status_id','=',3)
      ->get();

        return response()->json(['error'=> $error,'response'=> $response]);
    }


    public function updateProductsPrices($date){
      $response = "";
      $error = false;
      $response = DB::table('ml_data')
      ->select('ml_data.*','products.title','provider.provider_link','provider.asin')
      ->join('products', 'ml_data.id','=','products.ml_data_id')
      //->join('pictures','pictures.ml_data_id','=','ml_data.id')
      //->join('shipping','shipping.ml_data_id','=','ml_data.id')
      //->join('tags','tags.ml_data_id','=','ml_data.id')
      //->join('attributes','attributes.ml_data_id','=','ml_data.id')
      ->join('provider', 'provider.id','=','products.provider_id')
     // ->where('description','!=','null')
     // ->where('attributes.attributes_details','!=','{}')
      //->whereDate('ml_data.updated_at','2019-01-16')
      ->where('products.provider_id','!=',1)
      ->where('provider.status','=',NULL)
     ->whereRaw('date(ml_data.updated_at) ="2019-01-22" or date(ml_data.updated_at) ="2019-01-23"')
     //->take(100)
      ->get();
      $count = 0;
      foreach ($response as $r) {
        $count++;
      }
      return response()->json(['count'=> $count,'error'=>$error, 'response'=>$response]);
    }


    public function getProductApiCall($asin)
    {
             $ch = curl_init();
             $url = "https://scrapehero-amazon-product-info-v1.p.mashape.com/product-details
";
            curl_setopt($ch, CURLOPT_URL, $url . $method_request);
            // SSL important
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $output = curl_exec($ch);
            curl_close($ch);


            $this->response['response'] = json_decode($output);
    }

}
