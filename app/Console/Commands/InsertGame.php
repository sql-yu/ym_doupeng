<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\GameCategories;
use App\Models\GameImage;
use App\Models\GameTag;
use App\Models\MiddleGameCategories;
use App\Models\MiddleGameTag;
use Dflydev\DotAccessData\Data;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


// 获取游戏具体信息
class InsertGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'InsertGame';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get the game';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private $log = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        $this->insertData();
    }

    private function insertData()
    {

        $url = 'https://gamedistribution.com/games/';
        $gameModel = new Game();
        $whereList = [
            \DB::raw('type'), //type is null
        ];

        $limit = 100;

        foreach($whereList as $where){
            $gameModel = $gameModel->where($where);
        }

        $counts= $gameModel->count();
        $num = ceil($counts/$limit);
        for($i=0;$i<$num;$i++){
            $games = $gameModel
                ->limit($limit)
                ->get();
            dump(count($games));
            foreach ($games as $game) {
                $getUrl = '';
                if(!$game->get_url){
                    $gameNameArr = explode(' ', strtolower($game->game_name));
                    $gameNameStr = implode('-', $gameNameArr);
                    $getUrl = $url . $gameNameStr;
                }else{
                    $getUrl = $game->get_url;
                }

                $this->insertGameDetails($getUrl, $game);
                sleep(1);
            }
        }
    }

    private $html = '';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function insertGameDetails($url, $model)
    {
        if(!$this->getHtmlData($url)){
            return ;
        }
        $model->get_url = $url;
        $model->mobile_ready = $this->getMobileReady();
//        $w_h = $this->getDimensions();
//        $model->height = $w_h[0];
//        $model->width  = $w_h[1];
        $model->game_iframe = $this->getGameIframe();
//        $model->video_url = $this->getVideoUrl();
        $model->type = $this->getType();
        $res = $model->save();
        dump("{$model->id}:[type]{$model->type}:{$res}");
    }

    /**
     * 获取 游戏 url 中的 html 信息
     * @param $url
     * @return bool
     */
    private function getHtmlData($url)
    {
        $client = new Client();
        $html = '';
        try {
            $response = $client->get($url);
            $this->html = $response->getBody()->getContents();
            return true;
        } catch (\Exception $e) {
            dump($e->getMessage());
            return false;
        }
    }

    /**
     * 获取游戏名称
     * @return string
     */
    private function getGameName()
    {
        $regTitle = '/<h1 .*>(.*)<\/h1>/isU';
        $title = [];
        preg_match ($regTitle, $this->html, $title);
        return $title[1] ? strtolower($title[1]): '';
    }

    /**
     * 获取是否支持手机
     * @return int
     */
    private function getMobileReady()
    {
        $regMobileReady = '/<span class="label" data-v-726c6d26>Mobile ready<\/span>\s*<span .*>(.*)<\/span>/isU';
        $MobileReady = [];
        preg_match($regMobileReady, $this->html, $MobileReady);
        if (isset($MobileReady[1]) && $MobileReady[1] == 'Yes') {
            return 1;
        };
        return 0;
    }

    /**
     * 获取宽 width  高 height
     */
    private function getDimensions()
    {
        $regDimensions = '/<span class="label" data-v-726c6d26>Dimensions<\/span>\s*<span .*>(.*)<\/span>/isU';
        $Dimensions = [];
        preg_match($regDimensions, $this->html, $Dimensions);
        if (isset($Dimensions[1])) {
            $w_h = explode('x', $Dimensions[1]);
        } else {
            $w_h = [0, 0];
        }
        return $w_h;
    }

    /**
     * 获取游戏类型
     * @return string
     */
    private function getType()
    {
        //<span class="label" data-v-726c6d26="">Type</span>
        $regType = '/<span class="label" data-v-726c6d26>Type<\/span>\s*<span .*>(.*)<\/span>/isU';
        $Type = [];
        preg_match($regType, $this->html, $Type);
        return $Type[1] ?? '';
    }

    /**
     * 获取 游戏视频
     * @return mixed|string
     */
    private function getVideoUrl()
    {
        $video = [];
        $regVideo = '/<div class="walkthrough-ratio-container" data-v-726c6d26>\s*<iframe src="(.*)"/isU';
        preg_match($regVideo, $this->html, $video);
        return $video[1]??'';
    }

    /**
     * 获取游戏描述
     * @return mixed|string
     */
    private function getDescriptionContents()
    {
        $Description = [];
        $regDescription = '/<span class="label" data-v-726c6d26>Description<\/span>\s*<span .*>(.*)<\/span>/isU';
        preg_match($regDescription, $this->html, $Description);
        return $Description[1]??'';
    }

    /**
     * 获取游戏介绍
     * @return mixed|string
     */
    private function getInstruction()
    {
        $Instruction = [];
        $regDescription = '/<span class="label" data-v-726c6d26>Instruction<\/span>\s*<span .*>(.*)<\/span>/isU';
        preg_match($regDescription, $this->html, $Instruction);
        return $Instruction[1]??'';
    }

    /**
     * 获取游戏 h5地址
     * @return mixed|string
     */
    private function getGameLocation()
    {
        $Location = [];
        $regLocation = '/<label class="label company" data-v-726c6d26>Location<\/label>\s*<input .* value="(.*)"/isU';
        preg_match($regLocation, $this->html, $Location);
        return  $Location[1]??'';
    }

    /**
     * 获取游戏 嵌入地址
     * @return mixed|string
     */
    private function getGameIframe()
    {
        $Embed = [];
        $regEmbed = '/<label class="label" data-v-726c6d26>Embed<\/label>\s*<input .* value="(.*)"/isU';
        preg_match($regEmbed, $this->html, $Embed);
        return $Embed[1]??'';
    }

    /**
     * 获取游戏 分类
     * @param $gameId
     */
    private function getCategories($gameId){
        // 获取分类
        $Categories = [];
        $regCategories = '/<span class="label" data-v-726c6d26>Categories<\/span>\s*<span data-v-726c6d26>\s*(<a.*>(.*)<\/a>)+\s*<\/span>/isUm';
        preg_match_all($regCategories, $this->html, $Categories);


        $regC = '/(?<="pill" data-v-726c6d26>)[^<]+/';
        $cateList = [];
        if(isset($Categories[0]) && isset($Categories[0][0])){
            preg_match_all($regC, $Categories[0][0],$cateList);
            $insertCateId = [];
            $allCate = GameCategories::getNameId();
            foreach($cateList[0] as $cate){
                $cate = strtolower($cate);
                //  分类存在
                if(isset($allCate[$cate])){
                    $insertCateId[] = [
                        'game_id' => $gameId,
                        'categories_id' => $allCate[$cate],
                    ];
                }else{
                    // 分类不存在
                    $cateModel = new GameCategories();
                    $cateModel->game_cate_name = $cate;
                    $cateModel->save();
                    $insertCateId[] = [
                        'game_id' => $gameId,
                        'categories_id' => $cateModel->id,
                    ];
                }
            }
            $MiddleGameCategories = new MiddleGameCategories();
            $insertCate = $MiddleGameCategories->insert($insertCateId);
            if(!$insertCate){
                Log::channel('insert')->error($gameId .' [分类添加失败]');
            }
        }else{
            Log::channel('insert')->error($gameId .' [tag不存在]');
        }
    }

    /**
     * 获取游戏 标签
     * @param $gameId
     */
    private function getTag($gameId)
    {
        $regTags = '/<div class="tag-list" data-v-726c6d26>\s*(<a.*>(.*)<\/a>)+\s*<\/div>/isU';
        preg_match_all($regTags, $this->html, $Tags);
        $regT = '/(?<="pill" data-v-726c6d26>)[^<]+/';
        $tagList = [];
        if(isset($Tags[0]) && isset($Tags[0][0])){
            preg_match_all($regT, $Tags[0][0],$tagList);
//        dd($tagList[0]);

            $insertTagId = [];
            $allTag = GameTag::getNameId();
            foreach($tagList[0] as $tag){
                $tag = strtolower($tag);
                //  分类存在
                if(isset($allTag[$tag])){
                    $insertTagId[] = [
                        'game_id' => $gameId,
                        'tag_id' => $allTag[$tag],
                    ];
                }else{
                    // 分类不存在
                    $tagModel = new GameTag();
                    $tagModel->name = $tag;
                    $tagModel->save();
                    $insertTagId[] = [
                        'game_id' => $gameId,
                        'tag_id' => $tagModel->id,
                    ];
                }
            }
            $MiddleGameTag = new MiddleGameTag();
            $insertTag =  $MiddleGameTag->insert($insertTagId);
            if(!$insertTag){
                Log::channel('insert')->error($gameId.' [tag添加失败]');
            }
        }else{
            Log::channel('insert')->error($gameId.' [tag不存在]');
        }
    }

    /**
     * 获取游戏 图片集
     * @param $gameId
     */
    private function getImages($gameId)
    {
        $image = [];
        //<img src="https://img.gamedistribution.com/7222e36bad254a9893f67c48ef0d3801-1280x720.jpeg" width="1280" height="720" data-v-726c6d26="">
        $resImage = '/<img src="https:\/\/(.*)" width="([0-9]+)" height="([0-9]+)" data-v-726c6d26>/isU';
        preg_match_all($resImage, $this->html, $image);
        if(!isset($image[1])){
            $this->log->error($gameId.' [图片获取失败]');
            return;
        }
        $imageModel = new GameImage();
        $imageModel->game_id = $gameId;
        $imageCount = count($image[1]);
        for($i=0;$i<$imageCount;$i++){
            $filename = '_' . $image[2][$i] .'_' . $image[3][$i];
            $field  = 'img' . $filename;
            try {
                $file = file_get_contents('https://'.$image[1][$i]);
                $path = '/image/game/'. $gameId . $filename . '.jpeg';
                $imagePath = public_path($path);
                file_put_contents($imagePath,$file);
                $imageModel->$field = $path;
            }catch (\Exception $e){
                $imageModel->$field = '';
            }

        }
        $insertImage = $imageModel->save();
        if(!$insertImage){
            Log::channel('insert')->error($gameId.' [图片添加失败]');
        }
    }

}
