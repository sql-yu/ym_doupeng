<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;


class GameReptile extends Model
{
    public static $platform = [0=>'GD',1=>'ABC'];
    public static $status = [0=>'进行中',1=>'自动配置数据库信息中',2=>'已完成',3=>'失败'];


	use HasDateTimeFormatter;
    protected $table = 'game_reptile';

}
