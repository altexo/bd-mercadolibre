<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;

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
    //      return response()->json(['fail'=> 'Ok', 'posted' => $request->attributes], 500);
    // if (!$request->category_id) {
    //     return response()->json(['fail'=> 'Ok', 'posted' => $request], 500);
    // }
        $r = $this->convert_from_latin1_to_utf8_recursively($request);
    
     
        try{
            DB::transaction(function () use($r) {
                $ml_data_id = DB::table('ml_data')->insertGetId(
                    [   'ml_id' => $r->ml_id,
                        'category_id' => $r->category_id, 
                        'price' => $r->price,
                        'currency_id' => $r->currency_id,
                        'available_quantity' => $r->available_quantity,
                        'buying_mode' => $r->buying_mode,
                        'listing_type_id' => $r->listing_type_id,
                       // 'description' => $r->description['plain_text'],
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


               //Ciclo para recorrer el arreglo de atributos
                 $json_attributes = json_encode($r->attributes);
              
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
            });
        }catch( PDOException $e ){
            return response()->json(['err PDO'=> $e],500);
        }catch (\Exception $e){
            return response()->json(['err '=> new \Illuminate\Support\MessageBag(['catch_exception'=>$e])],500);
        }
        return response()->json(['success'=> 'Ok'], 500);
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


}
