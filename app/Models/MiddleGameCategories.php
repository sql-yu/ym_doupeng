<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class MiddleGameCategories extends Model
{
    protected $table = 'game_categories';

    public function images()
    {
        return $this->belongsTo(GameImage::class,'game_id','game_id');
    }

    public function gameInfo()
    {
        return $this->hasOne(Game::class,'id','game_id');
    }

}
