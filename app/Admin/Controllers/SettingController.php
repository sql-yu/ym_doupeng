<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;

class SettingController extends AdminController
{

    public $title ='追踪码设置';

    public function index(Content $content)
    {
        return $content
            ->translation($this->translation())
            ->title($this->title())
            ->description($this->description()['index'] ?? trans('admin.list'))
            ->body($this->form());
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $new = Setting::first();
        $formData =   Form::make();
        $formData->title('追踪码设置');
        $formData->hidden('id')->value($new->id);
        $formData->text('title','网站标题')->value($new->title);
        $formData->text('keywords','网站关键字')->value($new->keywords);
        $formData->text('description','网站描述')->value($new->description);
        $formData->text('copyright','版权展示')->value($new->copyright);
        
        $formData->text('desc_title','网站底部大标题')->value($new->desc_title);
        $formData->textarea('content_desc','网站底部描述')->value($new->content_desc);
        $formData->textarea('all_ads','全局谷歌追踪')->rows(15)->value($new->all_ads);
        $formData->textarea('index_ads','首页谷歌追踪')->rows(15)->value($new->index_ads);

        $formData->disableViewCheck();
        $formData->disableCreatingCheck();
        $formData->disableEditingCheck();
        $formData->action('setting/insert');
        return $formData;
    }

    public function insert()
    {
        $id = request()->get('id');
        $all_ads = request()->get('all_ads');
        $index_ads = request()->get('index_ads');
        $title = request()->get('title');
         $copyright = request()->get('copyright');
         $keywords = request()->get('keywords');
         $description = request()->get('description');
         $desc_title = request()->get('desc_title');
         $content_desc = request()->get('content_desc');
        
        if($id){
            $model = Setting::first();
        }else{
            $model = new Setting();
        }
        
        $model->all_ads = $all_ads??'';
        $model->index_ads = $index_ads??'';
        $model->title = $title??'';
        $model->copyright = $copyright??'';
           $model->keywords = $keywords??'';
        $model->description = $description??'';
        $model->desc_title = $desc_title??'';
        $model->content_desc = $content_desc??'';
        
        $res = $model->save();
        return $res ? Admin::json()->success('成功')->refresh() : Admin::json()->error('失败');
    }
}
