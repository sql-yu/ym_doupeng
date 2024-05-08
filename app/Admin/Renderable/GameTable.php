<?php


namespace App\Admin\Renderable;


use App\Models\Game;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;

class GameTable extends LazyRenderable
{
    public function grid(): Grid
    {
        $model = Game::where([
            'is_public' => 1,
            'mobile_ready' => 1,
        ])->orderBy('sort');
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('game_name','游戏名称');
            $grid->column('sort','排序');
            $grid->column('images','游戏主图')->display(function($v){
                return "/image/game/{$this->id}_300_300.webp";
            })->image('/',100,100);
            $grid->quickSearch(['id', 'game_name']);
            $grid->paginate(20);
            $grid->disableActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('game_name')->width(4);
            });
        });
    }
}
