<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'setting';
    public $timestamps = false;


    public static function getAll()
    {
        $data = self::first();
        return $data ? $data->toArray() : [];
    }
}
