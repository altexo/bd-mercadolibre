<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pictures extends Model
{
	protected $table = "pictures";    
	public $timestamps = false;
	protected $visible = ['url'];
}
