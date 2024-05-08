<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\GameCategories;
use App\Models\GameImage;
use App\Models\GameTag;
use App\Models\MiddleGameCategories;
use App\Models\MiddleGameTag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

// 从 gd rss 获取游戏的初步数据

class GetGameByRss extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetGameByRss';

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
        // dd('没有开启条件');
        for($i=50;$i<200;$i++){
            // 支持手机的游戏，类型全部，
            $url = "https://catalog.api.gamedistribution.com/api/v2.0/rss/All/?collection=all&categories=All&tags=All&subType=all&type=all&mobile=1&rewarded=all&amount=40&page={$i}&format=json";
            $this->insertRssGame($url);
            Log::channel('insert')->info($i . ' [执行完毕]');
            sleep(2);
        }
    }

    private function insertRssGame($modeUrl)
    {
        $urlData = '';
        // 游戏生成器，json格式，只获取手机端游戏
        try {
            $urlData = file_get_contents($modeUrl);
        }catch (\Exception $e){
            Log::channel('insert')->error($urlData . ' [内容获取失败]');
        }
        if(!$urlData){
            return;
        }
        $urlData = json_decode($urlData);
        foreach ($urlData as $item) {
            $game_name = strtolower($item->Title);
            $Description = $item->Description;
            $Instructions = $item->Instructions;
            $Mobile = intval($item->Mobile);
            $Height = $item->Height;
            $Width = $item->Width;
            $url = $item->Url;
            $images = $item->Asset;
            $Category = $item->Category;
            $Tag = $item->Tag;
            $Bundle = json_encode($item->Bundle);

            $model = Game::where(['game_name' => $game_name])->first();
            if ($model) {
                $model->mobile_ready = $Mobile;
                $model->height = $Height;
                $model->width = $Width;
                $model->bundle = $Bundle;
                $model->is_public = 1;
                $model->save();
            } else {
                $uniqueString = uniqid('', true); // 生成唯一字符串
                $uniqueNumber = substr(str_replace('.', '', $uniqueString), 0, 16);
                $model = new Game();
                $model->game_name = $game_name;
                $model->mobile_ready = $Mobile;
                $model->height = $Height;
                $model->width = $Width;
                $model->bundle = $Bundle;
                $model->is_public = 1;
                $model->game_location = $url;
                $model->description_contents = $Description;
                $model->instruction = $Instructions;
                $model->uuid_code = $uniqueNumber;
                $model->save();
                // 分类
                $insertCateId = [];
                $allCate = GameCategories::getNameId();
                foreach ($Category as $cate) {
                    $cate = strtolower($cate);
                    //  分类存在
                    if (isset($allCate[$cate])) {
                        $insertCateId[] = [
                            'game_id' => $model->id,
                            'categories_id' => $allCate[$cate],
                        ];
                    } else {
                        // 分类不存在
                        $cateModel = new GameCategories();
                        $cateModel->game_cate_name = $cate;
                        $cateModel->save();
                        $insertCateId[] = [
                            'game_id' => $model->id,
                            'categories_id' => $cateModel->id,
                        ];
                    }
                }
                $MiddleGameCategories = new MiddleGameCategories();
                $insertCate = $MiddleGameCategories->insert($insertCateId);
                if (!$insertCate) {
                    Log::channel('insert')->error($url . ' [分类添加失败]');
                }

                // tag
                $insertTagId = [];
                $allTag = GameTag::getNameId();
                foreach ($Tag as $tag) {
                    $tag = strtolower($tag);
                    //  分类存在
                    if (isset($allTag[$tag])) {
                        $insertTagId[] = [
                            'game_id' => $model->id,
                            'tag_id' => $allTag[$tag],
                        ];
                    } else {
                        // 分类不存在
                        $tagModel = new GameTag();
                        $tagModel->name = $tag;
                        $tagModel->save();
                        $insertTagId[] = [
                            'game_id' => $model->id,
                            'tag_id' => $tagModel->id,
                        ];
                    }
                }
                $MiddleGameTag = new MiddleGameTag();
                $insertTag = $MiddleGameTag->insert($insertTagId);
                if (!$insertTag) {
                    Log::channel('insert')->error($url . ' [tag添加失败]');
                }

            }
            
                            // 图片
            $imageModel = new GameImage();
            $imageModel->game_id = $model->id;
            foreach ($images as $img) {
                $b = explode('-', $img);
                if(!isset($b[1])){
                    continue;
                }
                $n_x = explode('.', $b[1]);
                $h_d = explode('x', $n_x[0]);
                $height = $h_d[0];
                $width = $h_d[1];
                $filename = '_' . $height . '_' . $width;
                $field = 'img' . $filename;
                try {
                    $file = file_get_contents($img);
                    $path = '/image/game/' . $model->id . $filename . '.jpeg';
                    $imagePath = public_path($path);
                    file_put_contents($imagePath, $file);
                    $imageModel->$field = $path;
                } catch (\Exception $e) {
                    $imageModel->$field = '';
                }
            }
            $insertImage = $imageModel->save();
            if (!$insertImage) {
                Log::channel('insert')->error($url . ' [图片添加失败]');
            }else{
                echo "$url | [图片更新成功]\n";
            }

            sleep(0);
        }
    }
}


