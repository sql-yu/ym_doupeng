<?php

namespace App\Models;

use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class GameSortTree extends Model
{
    use ModelTree;

    protected $table = 'game_sort_tree';

    protected $titleColumn = 'game_name';

    protected $orderColumn = 'sort';

    public function updateBatch($tableName, $page_id,$multipleData = [])
    {
        try {
            if (empty($multipleData)) {
                throw new \Exception ("数据不能为空");
            }

// 拼接 sql 语句
            $updateSql = "UPDATE " . $tableName . " SET ";
            $sets = [];
            $setSql = "" . "`sort`" . " = CASE ";
                foreach ($multipleData as $data) {
                    $setSql .= "WHEN " . "`id`" . " = ". "{$data['id']}" . " THEN {$data['sort']} ";
                }
            $setSql .= "ELSE " . 0 . " END ";
            $sets[] = $setSql;
            $updateSql .= implode(', ', $sets);
            $whereIn = array_column($multipleData,'id');
            $whereIn = implode(',',$whereIn);
            $updateSql = rtrim($updateSql, ", ") . " WHERE `id` IN (" . $whereIn . ") and page_id = {$page_id}";
// 传入预处理 sql 语句和对应绑定数据
            return \DB::update(\DB::raw($updateSql));
        } catch (\Exception $e) {
            return false;
        }
    }

}
