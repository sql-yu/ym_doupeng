<?php

namespace App\Console\Commands;

use App\Api\Controllers\ApiGameController;
use App\Helper\ImageHelper;
use App\Helper\Imgcompress;
use App\Models\GameImage;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;


// 转换压缩图片
class MakeLogDir extends Command
{
   
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MakeLogDir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
//        $this->t_image();
        $file = date("Y-m-d");
        for($i=0;$i<8;$i++){
            $logfile = date("Y-m-d",strtotime("+{$i} day"));
            $dir = storage_path("logs/{$logfile}/");
            if(!is_dir($dir)){
                try{
                    $result = File::makeDirectory($dir,0774);chmod($dir,0777);
                    echo "{$dir}创建完成,{$result}\n";
                }catch(\Exception $e){
                    echo $e->getMessage()."\n";
                }
                
            }
        }
        
        
    }
}
