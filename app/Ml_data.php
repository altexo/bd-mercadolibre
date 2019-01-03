<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Pictures;
use App\Shipping;
use App\tags;
use App\Products;
class Ml_data extends Model
{
    protected $table = "ml_data";
    public $timestamps = false;
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
}
