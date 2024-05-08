<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    public $table = "term";
	use HasDateTimeFormatter;    
    public $timestamps = false;

}
