<?php

namespace App\Admin\Controllers;

use App\Admin\Tools\GeneratePage;
use App\Models\GameReptile;
use App\Models\GameReptileList;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use GuzzleHttp\Client;

class GameReptileController extends AdminController
{

    protected $title = '信息采集';

    public function index(Content $content)
    {
        return $content
            ->translation($this->translation())
            ->title($this->title())
            ->description($this->description()['index'] ?? trans('admin.list'))
            ->body($this->grid());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new GameReptile(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('platform')->display(function($v){
                return GameReptile::$platform[$v];
            });
            $grid->column('status')->display(function($v){
                return GameReptile::$status[$v];
            })->label([
                1 => 'danger',
                2 => 'success',
                3 => 'primary',
            ]);
            $grid->column('reptile_url');
            $grid->column('reptile_game_name');
            $grid->column('remark','说明')->display(function($v){
                return "<pre>{$v}</pre>";
            });
            $grid->column('created_at');
            $grid->column('finish_at');
//            $grid->setActionClass(Grid\Displayers\Actions::class);
            $grid->actions(function(Grid\Displayers\Actions $actions){
                $url = admin_url('/reptileList/'.$actions->getKey());
                if($this->platform == 1){
                    $actions->append('<a href="'.$url.'"><i class="fa fa-eye">查看数据</i></a>');
                }
                $actions->disableEdit();
                $actions->disableQuickEdit();
                $actions->disableView();

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new GameReptile(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('platform');
            $show->field('status');
            $show->field('reptile_url');
            $show->field('reptile_game_name');
            $show->field('finish_at');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new GameReptile(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->select('platform')->options(GameReptile::$platform)->default(0);
            $form->url('reptile_url');
            $form->text('reptile_game_name');
            $form->display('created_at');
            $form->display('updated_at');
        });
    }


    // 采集列表详情
    protected function reptileList($id, Content $content)
    {
        $model = GameReptileList::with(['dbgame'])->where('reptile_id',$id);
        $grid = new Grid($model);
        $grid->column('game_name','采集游戏名称');
        $grid->column('game_id','数据库匹配-id')->editable();
        $grid->column('dbgame.game_name','数据库名称')->display(function($v){
            return $this->dbgame->game_name ?? '';
        });
        $grid->column('dbgame.image','数据库图片')->display(function($v){
            return $this->game_id ? "/image/game/{$this->game_id}_300_300.webp" : '';
        })->image('/',100,100);

        $grid->column('origin_url','目标链接');
        $grid->column('game_image','目标图片');
        $grid->paginate(100);
        $grid->disableActions();

        $grid->tools(new GeneratePage(null,$id));

        return $content
            ->title('采集信息列表')
            ->description()
            ->body($grid);
    }

    public function reptileEditOne($reptile_id,$reptile_list_id)
    {
        $data = request()->all();
        $res = GameReptileList::where('id',$reptile_list_id)->update(['game_id'=>$data['game_id']]);
        return $res ? Admin::json()->success('关联成功')->refresh() : Admin::json()->error('关联失败');
    }

}
