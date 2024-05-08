<?php

namespace App\Admin\Actions\Grid;

use App\Models\GameSort;
use Dcat\Admin\Grid\RowAction;
use Illuminate\Http\Request;

class DeleteSort extends RowAction
{
    public function title()
    {
        return '<i class="feather icon-delete"></i> 删除排序';
    }

    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要删除这行数据吗？",
            // 确认弹窗 content
            $this->row->username,
        ];
    }

    public function handle(Request $request)
    {
        // 获取当前行ID
        $id = $this->getKey();
        $sort  = GameSort::with('trees')->find($id);
        $sort->delete();

        // 返回响应结果并刷新页面
        return $this->response()->success("删除成功: [{$sort->page_no}]")->refresh();
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
