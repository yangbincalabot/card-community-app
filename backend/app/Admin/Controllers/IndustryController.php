<?php

namespace App\Admin\Controllers;

use App\Models\Industry;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;

class IndustryController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index()
    {
        return Admin::content(function(Content $content){
           $content->header('行业管理')
           ->body(Industry::tree());
        });
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('行业管理')
            ->description('编辑')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('行业管理')
            ->description('新增')
            ->body($this->form());
    }





    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Industry);

        $form->text('name', '行业名称')->rules('required');
        $form->select('parent_id', '父级')->options(Industry::selectOptions());
        $form->number('sort', '排序')->min(0)->default(50)->help('越小越靠前')->rules('required|min:0');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->footer(function (Form\Footer $footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
//            $footer->disableCreatingCheck();
        });

        return $form;
    }
}
