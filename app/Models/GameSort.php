<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GameSort extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'game_sort';

    public $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trees()
    {
        return $this->hasMany(GameSortTree::class,'page_id','id')->orderBy('sort');
    }
}
