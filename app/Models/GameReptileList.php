<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class GameReptileList extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'game_reptile_list';

    public function dbgame()
    {
        return $this->belongsTo(Game::class,'game_id','id');
    }
}
