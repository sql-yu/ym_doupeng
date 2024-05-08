<?php

namespace App\Admin\Actions\Grid;

use App\Models\GameSort;
use Dcat\Admin\Grid\RowAction;
use Illuminate\Http\Request;

class CopySort extends RowAction
{
    public function title()
    {
        return '<i class="feather icon-copy"></i> 复制排序';
    }

    protected $model;

    public function __construct(string $model = null)
    {
        $this->model = $model;
    }

    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要复制这行数据吗？",
            // 确认弹窗 content
            $this->row->username,
        ];
    }

    public function handle(Request $request)
    {
        // 获取当前行ID
        $id = $this->getKey();
        $sort  = GameSort::with('trees')->find($id);
        $clone = $sort->replicate();
        $clone->push();
        $clone->page_no .= '-copy';
        foreach($sort->getRelations() as $relation => $entries){
            foreach($entries as $entry){
                $e = $entry->replicate();
                $clone->trees()->save($e);
            }
        }

        // 返回响应结果并刷新页面
        return $this->response()->success("复制成功: [{$sort->page_no}]")->refresh();
    }

    /**
     * 设置要POST到接口的数据
     *
     * @return array
     */
    public function parameters()
    {
        return [];
    }

}
