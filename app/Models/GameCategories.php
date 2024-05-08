<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class GameCategories extends Model implements Sortable
{
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'categories';

    public static function getNameId()
    {
        $data = self::select(['id','game_cate_name'])->get()->toArray();
        return array_column($data,'id','game_cate_name');
    }

    public static function getIdName()
    {
        $data = self::select(['id','game_cate_name'])->get()->toArray();
        return array_column($data,'game_cate_name','id');
    }


}
