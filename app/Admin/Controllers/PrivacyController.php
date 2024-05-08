<?php

namespace App\Admin\Controllers;

use App\Models\AboutUs;
use App\Models\Privacy;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Database\Eloquent\Model;

class PrivacyController extends AdminController
{
    public $title ='隐身条款';

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
        $new = Privacy::first();
        $formData =   Form::make();
        $formData->title('隐身条款');
        $formData->hidden('id')->value($new->id??0);
        $formData->editor('des','隐身条款内容')->value($new->des??'');
        $formData->disableViewCheck();
        $formData->disableCreatingCheck();
        $formData->disableEditingCheck();
        $formData->action('privacy/insert');
        return $formData;
    }

    public function insert()
    {
        $id = request()->get('id');
        $about_us = request()->get('des');
        if($id){
            $model = Privacy::first();
        }else{
            $model = new Privacy();
        }
        $model->des = $about_us;
        $res = $model->save();
        return $res ? Admin::json()->success('成功')->refresh() : Admin::json()->error('失败');
    }
}
