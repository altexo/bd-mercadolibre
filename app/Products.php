<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = "products";

   // protected $visible = ['title'];
    public $timestamps = false;
}
