<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Pictures;
use App\Shipping;
use App\tags;
use App\Products;
use App\Attributes;
use App\Provider;
use Date;
use Nicolaslopezj\Searchable\SearchableTrait;
class Ml_data extends Model
{
    use SearchableTrait;
    protected $table = "ml_data";
   // public $timestamps = false;
    protected $searchable = [

    'columns' => [
        'products.title' => 10,
        'ml_data.ml_id' => 5,
        'provider.asin' => 8,
    ],
    'joins' => [
        'products' => ['ml_data.id','products.ml_data_id'],
        'provider' => ['products.provider_id', 'provider.id']
    ],
];

    public function pictures(){
    	return $this->hasMany(Pictures::class);
    }
    public function shipping(){
    	return $this->hasMany(Shipping::class);
    }
    public function tags(){
    	return $this->hasMany(tags::class);
    }

    public function products(){
    	return $this->hasMany(Products::class);
    }
    public function attributes(){
        return $this->hasMany(Attributes::class);
    }
    public function provider(){
        return $this->products()->belongsTo(Provider::class);
    }
    public function productsWhereDate(){
        $current_date =  new \DateTime();
        $date = $current_date->format('Y-m-d');
        return $this->hasMany(Products::class)->whereDate('updated_at','2019-01-04');
    }
}
