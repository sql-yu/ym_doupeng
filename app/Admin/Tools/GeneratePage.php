<?php


namespace App\Admin\Tools;


use App\Models\GameReptile;
use App\Models\GameReptileList;
use App\Models\GameSort;
use App\Models\GameSortTree;
use Dcat\Admin\Grid\Tools\AbstractTool;

class GeneratePage extends AbstractTool
{
    public $reptile_id;

    protected $style = 'btn btn-success waves-effect';


    public function __construct($title = null,$id = 0)
    {
        $this->reptile_id = $id;
        parent::__construct($title);
    }

    public function title()
    {
        return '生成投放页码';
    }
    public function confirm()
    {
        // 显示标题和内容
        return ['您确定要生产投放页面吗？', '请确认游戏都已经匹配,会自动过滤未匹配游戏'];
    }

    public function handle()
    {
        $reptile_id = request()->get('reptile_id');
        $reptile = GameReptile::where('id',$reptile_id)->first();
        if($reptile_id){
            $gameList = GameReptileList::with('dbgame')
                ->where('reptile_id',$reptile_id)
                ->where('game_id','>',0)
                ->get();
            $page_game = [];
            foreach($gameList as $item){
                $page_game[] = [
                    'id' => $item->id,
                    'game_name' => $item->dbgame->game_name,
                    'image' => '/image/game/' . $item->id . "_300_300.webp",
                ];
            }
            $sort = new GameSort();
            $sort->cate = 0;
            $sort->page_no = uniqid();
            $sort->desc = $reptile->name . '-采集生产页面';
            $sort->game_sort = json_encode($page_game);
            $sort->save();
            $i = 1;
            foreach($page_game as &$v){
                $v['game_id'] = $v['id'];
                unset($v['id']);
                $v['page_id'] = $sort->id;
                $v['sort'] = $i;
                $i++;
            }
            unset($v);
            $res = (new GameSortTree())->insert($page_game);
            return $res ? $this->response()->success('发送成功')->redirect(admin_url("gamesort/editTree/{$sort->id}")):$this->response()->error('生产失败');
        }
        return $this->response()->error('生产失败');
    }

    /**
     * 设置请求参数
     *
     * @return array|void
     */
    public function parameters()
    {
        return [
            'reptile_id' => $this->reptile_id
        ];
    }


}
