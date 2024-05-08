<?php

namespace App\Console\Commands;

use App\Helper\ImageHelper;
use App\Models\Game;
use App\Models\GameCategories;
use App\Models\GameReptileList;
use App\Models\GameTag;
use App\Models\MiddleGameCategories;
use App\Models\MiddleGameTag;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\GameReptile as GRModel;
use Illuminate\Support\Facades\Log;

class GameReptile extends Command
{

    use ImageHelper;

    private $gd_url = 'https://gamedistribution.com/games/';
    private $abc_url = 'https://abcalphagame.com';
    private $html;
    private $remark;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamereptile';

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
        //  gd 平台单个游戏采集
        $reptileGD = \App\Models\GameReptile::where([
            'platform' => 0, // gd
            'status' => 0,
        ])->first();
        if($reptileGD){
            $this->reptileGD($reptileGD);
        }

        // abc 页面采集 第一步 先采集 页面的 游戏title 跟 图片信息
        $reptileABC = \App\Models\GameReptile::where([
            'platform' => 1, // abc
            'status' => 0,
        ])->first();

        if($reptileABC){
            $this->reptileABC($reptileABC);
        }

        // abc 页面采集 第二步 讲对应 game_reptile_list 进行数据库匹配
        $reptileABC2 = \App\Models\GameReptile::where([
            'platform' => 1, // abc
            'status' => 1,
        ])->first();
        if($reptileABC2){
            $this->reptileABC2($reptileABC2);
        }
    }

    public function reptileABC($reptileModel)
    {
        $client = new Client(['verify'=>false]);
        $response = $client->get($reptileModel->reptile_url);
        $html = $response->getBody()->getContents();
        $regUrl = '/var preset =(.*);/isU';
        $gameList = [];
        preg_match ($regUrl,$html, $gameList);
        $InsertGames = [];
        if(isset($gameList[1])){
            $gameListArray = json_decode($gameList[1],true);
            foreach($gameListArray['m_logo'] as $game){
                $InsertGames[] = [
                    'reptile_id' => $reptileModel->id,
                    'game_name' => $game['title'],
                    'origin_url' => $this->abc_url . $game['href'],
                    'game_image' => $this->abc_url . $game['logo'],
                ];
            }
            foreach($gameListArray['s_logo'] as $game){
                $InsertGames[] = [
                    'reptile_id' => $reptileModel->id,
                    'game_name' => $game['title'],
                    'origin_url' => $this->abc_url . $game['href'],
                    'game_image' => $this->abc_url . $game['logo'],
                ];
            }
            DB::beginTransaction();
            try {
                GameReptileList::insert($InsertGames);
                $reptileModel->status = 1;
                $reptileModel->save();
                DB::commit();
            }catch (\Exception $e)
            {
                DB::rollBack();
            }
        }
    }

    private function reptileABC2($reptileModel)
    {
        $num_success = 0;
        $num_error = 0;
        $reptileModel_list = GameReptileList::where('reptile_id',$reptileModel->id)
            ->get();
        foreach($reptileModel_list as $item){
            $a = Game::where('game_name','like',"%{$item->game_name}%")->first();
            if($a){
                $item->game_id = $a->id;
                $item->save();
                $num_success++;
            }else{
                $num_error++;
            }
        }
        $total = $num_success + $num_error;
        $reptileModel->status = 2;
        $reptileModel->remark = "总计:{$total}个\n匹配成功:{$num_success}个\n匹配失败:{$num_error}个";
        $reptileModel->finish_at = date('Y-m-d H:i:s');
        $reptileModel->save();
    }

    public function reptileGD($reptileModel)
    {
        $game_name = strtolower($reptileModel->reptile_game_name);
        $queryselect = implode('-',explode(' ',$game_name));
        $gdUrl = $this->gd_url . $queryselect;
        if(!$this->getHtmlData($gdUrl)){
            $this->remark .= "目标网页访问失败,终止采集";
            return $this->remark($reptileModel,3);
        }
        $gameModel = new Game();
        $gameModel->game_name = $this->getGameName();
        $gameModel->game_iframe = $this->getGameIframe();
        $gameModel->game_location = $this->getGameLocation();
        $gameModel->is_public = 1;
        $gameModel->description_contents = $this->getDescriptionContents();
        $gameModel->instruction = $this->getInstruction();
        $gameModel->get_url = $gdUrl;
        $gameModel->mobile_ready = $this->getMobileReady();
        $gameModel->type = $this->getType();
        $w_h = $this->getDimensions();
        $gameModel->height = $w_h[0];
        $gameModel->width  = $w_h[1];
        $res = $gameModel->save();
        if($res){
            $this->remark .= "[ID]:{$gameModel->id}\n";
        }else{
            $this->remark .= "采集失败,终止采集";
            return $this->remark($reptileModel,3);
        }
        $this->getCategories($gameModel->id);
        $this->getTag($gameModel->id);
        $this->getImages($gameModel->id);

        $reptileModel->remark = $this->remark;
        $reptileModel->status = 2;
        $reptileModel->finish_at = date('Y-m-d H:i:s');
        $reptileModel->save();
        return $this->remark($reptileModel,2);
    }

    private function remark($reptileModel,$status)
    {
        $reptileModel->remark = $this->remark;
        $reptileModel->status = $status;
        $reptileModel->finish_at = date('Y-m-d H:i:s');
        $reptileModel->save();
        return true;
    }

    private function getHtmlData($url)
    {
        $client = new Client(['verify'=>false]);
        $html = '';
        try {
            $response = $client->get($url);
            $this->html = $response->getBody()->getContents();
            return true;
        } catch (\Exception $e) {
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
                $this->remark .= "[分类]添加失败\n";
                Log::channel('insert')->error($gameId .' [分类添加失败]');
            }else{
                $this->remark .= "[分类]添加成功\n";
            }
        }else{
            Log::channel('insert')->error($gameId .' [tag不存在]');
            $this->remark .= "[分类]不存在\n";
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
                $this->remark .= "[tag]添加失败\n";
                Log::channel('insert')->error($gameId.' [tag添加失败]');
            }else{
                $this->remark .= "[tag]添加成功\n";
            }
        }else{
            Log::channel('insert')->error($gameId.' [tag不存在]');
            $this->remark .= "[tag]不存在\n";
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
        if(isset($image[1]) && isset($image[1][0])){
            try {
                $file = file_get_contents('https://'.$image[1][0]);
                $fileName = $gameId .'_'. $image[2][0] . '_' . $image[3][0] . '.jpeg';
                $download = public_path('image/down/'.$fileName);
                file_put_contents($download,$file);
                $path = '/image/game/'. $gameId . '_300_300.webp';
                $imagePath = public_path($path);
                $this->transform_image($download,'webp',$imagePath);
                $result = $this->resize_image($imagePath,$imagePath,300,300);
                if($result){
                    unlink($download);
                }
                $this->remark .= "[image]{$path}\n";
            }catch (\Exception $e){
                $this->remark .= "[image]失败{$e->getMessage()}\n";
            }
        }
    }
}
