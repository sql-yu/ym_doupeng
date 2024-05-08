<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GameTag extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'tag';

    public static function getNameId()
    {
        $data = self::select(['id','name'])->get()->toArray();
        return array_column($data,'id','name');
    }
}
