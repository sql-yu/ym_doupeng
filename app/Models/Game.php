<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Game extends Model  implements Sortable
{
    use HasDateTimeFormatter;
    use SoftDeletes;
    use SortableTrait;

    protected $table = 'game';

    public $guarded = [];

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    public function categories()
    {
        return $this->belongsToMany(GameCategories::class,'game_categories','game_id','categories_id');
    }

    public function images()
    {
        return $this->hasOne(GameImage::class,'game_id');
    }

}
