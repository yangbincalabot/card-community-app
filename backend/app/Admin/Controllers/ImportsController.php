<?php

namespace App\Admin\Controllers;

use App\Imports\CarteImport;
use App\Models\Imports;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Maatwebsite\Excel\Facades\Excel;

class ImportsController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('导入记录')
            ->description('列表')
            ->body($this->grid());
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
            ->header('新增导入')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Imports);

        $grid->id('Id');
        $grid->file_url('导入文件');
        $grid->created_at('导入时间');
//        $grid->disableActions();
        $grid->disableFilter();
        $grid->disableRowSelector();
//        $grid->disableTools();
        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
//            $actions->disableDelete();
        });
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Imports);

        $form->file('file_url', '导入文件');

        $Excel = new Excel();
        $form->saved(function (Form $form) use ($Excel) {
            $file_url = 'storage/'.$form->model()->file_url;
            Excel::import(new CarteImport, $file_url);
            \Log::info($file_url);
        });
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        return $form;
    }
}
