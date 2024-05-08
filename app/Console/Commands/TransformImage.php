<?php

namespace App\Console\Commands;

use App\Api\Controllers\ApiGameController;
use App\Helper\ImageHelper;
use App\Helper\Imgcompress;
use App\Models\GameImage;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;


// 转换压缩图片
class TransformImage extends Command
{
    use ImageHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TransformImage';

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
        $this->checkoutImageId();
    }

    private function transform()
    {
        $api = new ApiGameController();
        $games = $api->getGameList(['type'=>'html5']);
        foreach($games as $game)
        {
            $info = explode('/',$game['image']);
            $image_name = $info[count($info)-1]; // 1_512_512.jpeg
            $field_info = explode('.',$image_name); // [1_512_512,jpeg]
            if($field_info[1] == 'webp'){
                continue;
            }
            $image_path = public_path('image/game/'.$field_info[0].'.'.'jpeg');
            if(!file_exists($image_path)){
                dump($game['id']);
                continue;
            }
            $db_field = explode('_',$field_info[0]);  //[1,512,512]
            $img_field = 'img_'.$db_field[1]. '_' . $db_field[2]; //img_512_512
            $image_new_path = public_path('image/game2/'.$field_info[0].'.webp');
            $a = $this->transform_image($image_path,'webp',$image_new_path);
            if($a){
                $imageModel = new GameImage();
                $imageModel->where(['game_id'=>$game['id']])->update([
                    $img_field => '/image/game2/'.$game['id'] .'_' .$db_field[1]. '_' . $db_field[2] .'.webp',
                ]);
            }
        }
    }

    private function buquan()
    {
        $api = new ApiGameController();
        $games = $api->getGameList(['type'=>'html5']);
        foreach($games as $game){
            $image_path = $game['image'];
            $img_info = explode('/',$image_path);
            $img_name = $img_info[count($img_info)-1];
            $img_path = public_path('image/game2/'.$img_name);
            if(!file_exists($img_path)){
                $img_array = explode('.',$img_name);
                $old_path = public_path('image/game/'.$img_array[0].'.jpeg');
                $this->transform_image($old_path,'webp',$img_path);
            }
        }

    }

    private $image_w_d = [
        '_512_512',
        '_512_384',
        '_512_340',
        '_1280_550',
        '_1280_720',


    ];

    // 把 game 目录中的 jpeg 转化到 game3 中 webp
    private function t_image()
    {
        $public_path = public_path('image/game_webp/');
        $new_public_path = public_path('image/game/');

        for ($i=1;$i<5000 ; $i++) {
            foreach ($this->image_w_d as $wd) {
                $file = $public_path . $i . $wd . '.webp';
                if(file_exists($file)){
                    $newFile = $new_public_path . $i . '_300_300' . '.webp';
//                    $a = $this->transform_image($file, 'webp', $newFile);
//                    $yasuo = $new_public_path . $i . $wd . '.';
//                    $img  = new Imgcompress($newFile,1);
//                    $img->compressImg($yasuo);
                    $a = $this->resize_image($newFile,$file,300,300);
                    if($a){
                        if($wd != '_512_512'){
                            dump($i);
                        }
                    }
                    break;
                }else{
                    dump($file."不存在");
                }
            }
        }
    }


    private function checkoutImageId()
    {
        $path = '/Users/reverse/Desktop/game_111/';
        $files = scandir($path);
        $fileItem = [];
        foreach($files as $v) {
            if ($v == '.DS_Store' || $v == '..'){
                continue;
            }
            $newPath = $path .DIRECTORY_SEPARATOR . $v;
            if(is_file($newPath)){
                $fileItem[] = $newPath;
            }
        }
        foreach($fileItem as $file)
        {
            $fileInfo = pathinfo($file);
            $newFile = '/Users/reverse/Desktop/game_222/';
            $fileName = $fileInfo['filename']; //1022_512_512
            $new_file_name = explode('_',$fileName);
            $new_file_path = $newFile . $new_file_name[0] . "_300_300.webp";

            // 压缩
//            $yasuo = $newFile . $fileName . '.';
//            $img  = new Imgcompress($file,1);
//            $img->compressImg($yasuo);

            // 改大小
            $a = $this->resize_image($new_file_path,$file,300,300);
        }
    }
}
