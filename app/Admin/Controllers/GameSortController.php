<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\CopySort;
use App\Admin\Actions\Grid\DeleteSort;
use App\Admin\Renderable\GameTable;
use App\Models\Game;
use App\Models\GameCategories;
use App\Models\GameSort;
use App\Models\MiddleGameCategories;
use App\Models\Setting;
use App\Models\GameSortTree;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Show;
use Dcat\Admin\Tree;
use Dcat\Admin\Widgets\Table;

class GameSortController extends AdminController
{
    protected $title = '投放页游戏排列';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new GameSort(), function (Grid $grid) {
            $grid->column('id')->hide();
            $grid->column('page_no','页码');
            $grid->column('cate','分类');
            $grid->column('desc');
            $grid->html('sort','排序')->display(function($v){
               return "查看";
            })->link(function() {
                return admin_url("/gamesort/editTree/{$this->id}");
            });
            $grid->column('is_recommend','推荐位')->switch();
            $grid->column('created_at');
            $grid->disableViewButton();
            $grid->disableBatchDelete();
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // append一个操作
                $actions->append(new CopySort());
                $actions->append(new DeleteSort());
            });
            $grid->disableDeleteButton();
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
        return Show::make($id, new GameSort(), function (Show $show) {
            $show->field('id');
            $show->field('desc');
            $show->field('game_sort');
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
        $form =  new Form();
        $form->display('id');
        $form->text('page_no','页码');
        $form->text('desc');
        $form->select('cate','分类')->options(GameCategories::getIdName());
        $form->multipleSelectTable('game_sort', '依次选择')
            ->title('游戏列表')
            ->from(GameTable::make())
            ->model(Game::class, 'id', 'game_name')
        ;
        $form->textarea('ads','页面追踪码');
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        return $form;

//        return "<div style='padding:10px 8px'>{$form->render()}</div>";
    }

    public function store()
    {
        $request = request()->all();
        $desc = $request['desc']??'';
        $page_no = $request['page_no'];
        $cate = intval($request['cate']);
        $game_sort = explode(',',$request['game_sort']);

        if(!$game_sort && !$page_no){
           return Admin::json()->error('请填写游戏序列和页码');
        }
        if($cate){
            $ids = MiddleGameCategories::select(['game_id','game_name'])
                ->leftJoin('game','game.id','=','game_categories.game_id')
                ->where('categories_id','=',$cate)
                ->where('game.type','=','html5')
                ->where('game.is_public','=',1)
                ->where('game.mobile_ready','=',1)
                ->whereNotIn('game_id',$game_sort)
                ->limit(101-count($game_sort))->get()->toArray();
        }else{
            $ids = Game::select(['id','game_name'])
            ->where('game.type','=','html5')
            ->where('game.is_public','=',1)
            ->where('game.mobile_ready','=',1)
            ->whereNotIn('game_id',$game_sort)
            ->limit(101-count($game_sort))->get()->toArray();
        }
        $game_sort = Game::whereIn('id',$game_sort)->select(['id as game_id','game_name'])->get()->toArray();
        $jsonArray = array_merge($game_sort,$ids);

        $model = new GameSort();
        $model->desc = $desc??'';
        $model->cate = $cate;
        $model->page_no = $page_no;
        $model->ads = $request['ads']??'';
        $res = $model->save();

        foreach($jsonArray as &$ar){
            $ar['image'] = "/image/game/{$ar['game_id']}_300_300.webp";
            $ar['page_id'] = $model->id;
        }
        $tree = new GameSortTree();
        $tree->insert($jsonArray);

        return $res ? Admin::json()->success('成功')->redirect('/gamesort') : Admin::json()->error('失败');
    }


    public function updaterecommend($id)
    {
        $model = new GameSort();
        $res = $model->where('id','>',0)->update(['is_recommend'=>0]);
        $res = GameSort::where('id',$id)->update(['is_recommend'=>1]);
        return $res === false ? Admin::json()->error('失败') : Admin::json()->success('成功')->refresh() ;
    }

    public function destroy($id)
    {
        $res = GameSort::where(['id'=>$id])->delete();
        return $res ? Admin::json()->success('成功')->refresh() : Admin::json()->error('失败');
    }

    protected function editForm($id)
    {
        $data = GameSort::find($id);

        $form =  new Form();
        $form->title('基本设置');
        $form->hidden('id')->value($data->id);
        $form->text('page_no','页码')->value($data->page_no);
        $form->text('desc')->value($data->desc);
        $form->select('cate','分类')->options(GameCategories::getIdName())
            ->value($data->cate);
        $form->textarea('ads','页面追踪码')->value($data->ads);
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->action('/gamesort/updateJiben');
        return $form;
    }


    public function edit($id, Content $content)
    {
        $tree = new Tree(\App\Admin\Repositories\GameSortTree::class);
        $tree->query(function($query)use($id){
            return $query->where('page_id',$id);
        });
        $tree->branch(function($branch){
            $logo = "<img src='/image/game/{$branch['game_id']}_300_300.webp' style='max-width:30px;max-height:30px' class='img'/>";
            return "sort - {$branch['sort']} - game_id -{$branch['game_id']} - {$branch['game_name']} $logo";
        });
        $tree->disableCreateButton();
        $tree->disableQuickCreateButton();
        $tree->disableEditButton();
        $tree->disableQuickEditButton();
        $tree->disableDeleteButton();
// //<i class="feather icon-trash"></i>
        $tree->maxDepth(1);
        $tree->setResource("/gamesort/editTree/{$id}");

        return $content
            ->translation($this->translation())
            ->title($this->title())
            ->description($this->description()['edit'] ?? trans('admin.edit'))
            ->body($this->editForm($id))->body($tree->render());
    }

    public function updateJiben()
    {
        $request = request()->all();
        $id = $request['id'];
        $page_no = $request['page_no'];
        $desc = $request['desc']??'';
        $cate = intval($request['cate']);
        $ads = $request['ads'];
        $model = GameSort::find($id);
        $model->desc = $desc??'';
        $model->cate = $cate;
        $model->page_no = $page_no;
        $model->ads = $ads??'';
        $res = $model->save();
        return $res ? Admin::json()->success('成功')->redirect('/gamesort') : Admin::json()->error('失败');
    }

    public function editTree($id,Content $content)
    {
        $sort = GameSort::find($id);
        $row = new Row();
        $model = new \App\Admin\Repositories\GameSortTree();
        $tree = new Tree($model);
        $tree->query(function($query)use($id){
            return $query->where('page_id',$id);
        });
        $tree->branch(function($branch){
            $logo = "<img src='/image/game/{$branch['game_id']}_300_300.webp' style='max-width:30px;max-height:30px' class='img'/>";
            return "sort - {$branch['sort']} - game_id -{$branch['game_id']} - {$branch['game_name']} $logo";
        });
        $tree->disableCreateButton();
        $tree->disableQuickCreateButton();
        $tree->disableEditButton();
        $tree->maxDepth(1);
        $tree->setResource("/gamesort/editTree/{$id}");
        $tree->disableQuickEditButton();
        $tree->disableDeleteButton();
        $row->column(12,$tree);

        return $content
            ->translation($this->translation())
            ->title($sort->des)
            ->description("页面:{$sort->page_no},备注:{$sort->desc}")
            ->body($row);
    }

    public function editTreePost($id)
    {
        $request = request()->all();
        $order = @json_decode($request['_order'],true);
        $sort = 1;
        foreach($order as &$o)
        {
            $o['sort'] = $sort;
            $sort++;
        }
        unset($o);
        $model = new GameSortTree();
        $res =  $model->updateBatch('game_sort_tree',$id,$order);
        return $res === false ? Admin::json()->error('失败') : Admin::json()->success('成功')->refresh() ;
    }

    public function create(Content $content)
    {
        return $content
            ->translation($this->translation())
            ->title($this->title())
            ->description($this->description()['create'] ?? trans('admin.create'))
            ->body($this->form());
    }

}
