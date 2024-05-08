<?php

namespace App\Admin\Controllers;

use App\Models\GameCategories;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class GameCateController extends AdminController
{
    
    public $title = '游戏分类';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new GameCategories(), function (Grid $grid) {
            if (request()->get('_view_') !== 'list') {
                // 设置自定义视图
                $grid->view('admin.grid.gametag');
                $grid->setActionClass(Grid\Displayers\Actions::class);
            }

            $grid->tools([
//                $this->buildPreviewButton('btn-primary'),
//                new SwitchGridView(),
            ]);

            $grid->column('id')->sortable();
            $grid->column('game_cate_name');
            $grid->column('game_cate_name_cn');
            $grid->column('sort');
            $grid->column('cate_image')->image('/',100,100);
            $grid->column('created_at');
            $grid->disableViewButton();
        });
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $model = new GameCategories();
        return Form::make($model, function (Form $form) {
            $form->display('id');
            $form->text('game_cate_name');
            $form->text('game_cate_name_cn')->saving(function($v){
                return $v??"";
            });
            $form->image('cate_image','分类封面图')
                ->move('/cate')
                ->autoSave(false)
                ->uniqueName()
                ->url('users/files')
                ->autoUpload()->disk('admin')->withFormData(['dir'=>'cate','game_id'=>$form->getKey()])->saving(function($v){
                    return $v??"";
                });;
        });
    }
}
