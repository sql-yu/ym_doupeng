<?php

namespace App\Admin\Controllers;

use App\Helper\ImageHelper;
use App\Models\Game as GameModel;
use App\Models\GameCategories;
//use Dcat\Admin\Form;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use App\Admin\Actions\Form\Next;
use App\Admin\Actions\Form\Pre;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Contracts\Support\Renderable;


/*
 * 数据库刷新uuid
-- DROP PROCEDURE IF EXISTS update_uuid;
--
-- CREATE PROCEDURE update_uuid()
-- BEGIN
--     DECLARE i INT DEFAULT 1;
--     DECLARE total_rows INT;
--     SELECT COUNT(*) INTO total_rows FROM game;
--     WHILE i <= total_rows DO
--         UPDATE game
--         SET uuid_code = SUBSTRING(MD5(RAND()), 1, 9),
-- 				uuid_2_code = CONCAT(SUBSTRING(MD5(RAND()), 9, 5),CHAR(FLOOR(97 + RAND() * 26)))
--         WHERE id = i;
--         SET i = i + 1;
--     END WHILE;
-- END

-- CALL update_uuid();
 */

class GameController extends AdminController
{

    use ImageHelper;

    public $title = '游戏管理';
    private $weburl = '';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = GameModel::orderBy('sort');
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('game_name')->limit(10, '...');
            $grid->column('urls', 'b面连接')->display(function () {
                return "<span style='color:mediumvioletred'>介绍页：$this->game_clock_url</span>
<br>
<span style='color:#0e1b9f'>详情页：$this->detail_clock_url</span>";
            });
//            $grid->column('images','游戏主图')->display(function($v){
//                return $v['img_512_512']??'';
//            })->image('/',100,100);
//            $grid->column('categories','游戏分类')->display(function($v){
//                return array_column($v->toArray(),'game_cate_name');
//            })->explode()->label();
//            $grid->column('is_public','上架')->switch();
//            $grid->column('sort')->orderable();
//            $grid->column('sort')->editable(true);
//            $grid->column('created_at');
            $grid->column('kids_1', '像素')->display(function () {
                return "<span style='color:mediumvioletred'>介绍页-kw：$this->kwai_id</span>
<br>
<span style='color:mediumvioletred'>介绍页-tt：$this->tt_id</span>
<br>
<span style='color:#0e1b9f'>详情页-kw：$this->kwai_detail_id</span>
<br>
<span style='color:#0e1b9f'>详情页-tt：$this->tt_detail_id</span>";
            });
            $grid->disableViewButton();
            $grid->disableCreateButton();
            $grid->quickSearch(function ($model, $query) {
                $query = trim($query, '/');
                if (substr($query, 0, 4) === 'http') {
                    $matches = array();
                    if (preg_match('/jogo\/(.+)$/', $query, $matches)) {
                        $extractedString = $matches[1];
                        $query = $extractedString; // 输出提取的字符串
                    }
                }
                $model->where('uuid_code', $query);
            })->width(50);
            $grid->paginate(30);
            $grid->withBorder();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $app_game_page = env("APP_GAME_PAGE");
        $app_detail_page = env("APP_DETAIL_PAGE");
        $gameModel = GameModel::with(['categories', 'images']);
        $form = Form::make($gameModel);
        $form->hidden('id');
        $form->divider();


        $form->column(6, function (Form  $form)use($app_game_page) {
            $form->display('uuid_code', '投放链接')->with(
                function ($value)use($app_game_page) {
                    return 'https://开户域名/'.$app_game_page .'/' . $value.'-'.$this->uuid_2_code . $this->id;
                }
            );

            $form->switch('game_clock', 'B面')->default(0)->help('开启后符合条件会跳转b面');
            $form->switch('game_send_purchase', '介绍页购买事件监控')->default(1)->help('开启后有purchase,addToCart事件');
            $form->url('game_clock_url', '介绍页b面链接');
            $form->text('game_country', '介绍页允许国家')->help('默认bra  英文逗号隔开，添加*表示无限制,菲律宾phl')->value('bra');
            $form->text('kwai_id', "介绍页面kwai像素id");
            $form->text('tt_id', "介绍页面tiktok像素id");

        });
        $form->column(6, function (Form $form)use($app_detail_page){
            $form->display('uuid_code', '投放链接')->with(
                function ($value)use($app_detail_page) {
                    return 'https://开户域名/'.$app_detail_page .'/' . $value.'-'.$this->uuid_2_code . $this->id;
                }
            );

            $form->switch('detail_clock', 'B面')->default(0)->help('开启后符合条件会跳转b面');
            $form->switch('detail_send_purchase', '详情页购买事件监控')->default(1)->help('开启后有purchase,addToCart事件');
            $form->url('detail_clock_url', '详情页b面链接');
            $form->text('detail_country', '详情页允许国家')->help('默认bra 英文逗号隔开，添加*表示无限制,菲律宾phl')->value('bra');
            $form->text('kwai_detail_id', "详情页kwai像素id");
            $form->text('tt_detail_id', "详情页tiktok像素id");



//            $form->image('image','图_300_300')
//                ->move('/game')
//                ->autoSave(false)
//                ->uniqueName()
//                ->url('users/files')
//                ->autoUpload()
//                ->disk('admin')
//                ->withFormData(['dir'=>'game','imgsize'=>'_300_300','data_id'=>$gameId])
//            ;
//            $form->image('images.img_512_384','图_512_384')
//                ->move('/game')
//                ->autoSave(false)
//                ->uniqueName()
//                ->url('users/files')
//                ->autoUpload()->disk('admin')->withFormData(['dir'=>'game','imgsize'=>'_512_384','game_id'=>$form->getKey()]);
//            $form->image('images.img_512_512','图_512_512')
//                ->move('/game')
//                ->autoSave(false)
//                ->uniqueName()
//                ->url('users/files')
//                ->autoUpload()->disk('admin')->withFormData(['dir'=>'game','imgsize'=>'_512_512','game_id'=>$form->getKey()]);
//            $form->image('images.img_1280_550','图_1280_550')
//                ->move('/game')
//                ->autoSave(false)
//                ->uniqueName()
//                ->url('users/files')
//                ->autoUpload()->disk('admin')->withFormData(['dir'=>'game','imgsize'=>'_1280_550','game_id'=>$form->getKey()]);
//            $form->image('images.img_1280_720','图_1280_720')
//                ->move('/game')
//                ->autoSave(false)
//                ->uniqueName()
//                ->url('users/files')
//                ->autoUpload()->disk('admin')->withFormData(['dir'=>'game','imgsize'=>'_1280_720','game_id'=>$form->getKey()]);
//            $form->text('game_name');
//            $form->switch('is_public')->default(1);
//            $form->text('game_iframe');
//            $form->text('game_location');
//            $form->multipleSelect('categories','分类')
//                ->options(GameCategories::all()->pluck('game_cate_name', 'id'))
//                ->customFormat(function ($v) {
//                    if (! $v) {
//                        return [];
//                    }
//                    // 从数据库中查出的二维数组中转化成ID
//                    return array_column($v, 'id');
//                });
//            ;
//            $form->number('sort');
//            $form->textarea('description_contents');
//            $form->textarea('instruction');
//            $form->textarea('game_reviews_contents');
//            $form->text('game_reviews_end');

        });



        $form->disableDeleteButton();
        $form->defaultEditingChecked();
        $form->disableViewButton();
        $form->disableListButton();
        $form->tools(function (Form\Tools $tools) {
            $tools->append(new Pre());
            $tools->append(new Next());
        });
        return $form;
    }

    public function update($id)
    {
        $data = request()->all();
        return $this->form()->update($id, $data); // TODO: Change the autogenerated stub
    }
}
