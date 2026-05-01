<?php

namespace App\Admin\Controllers;

use App\Models\Agent;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AgentController extends Controller
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
            ->header('代理商等级管理')
            ->description('列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('代理商等级管理')
            ->description('description')
            ->body($this->detail($id));
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
            ->header('代理商等级管理')
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
            ->header('代理商等级管理')
            ->description('新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Agent);
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', 'ID')->sortable();
        $grid->column('name', '名称');
        $grid->column('price', '价格（元）');
        $grid->column('recommend_level_one', '一级推荐奖励')->display(function ($recommend_level_one){
            return $recommend_level_one . '%';
        });
        $grid->column('recommend_level_two', '二级推荐奖励')->display(function ($recommend_level_two){
            return $recommend_level_two . '%';
        });
        $grid->column('introduce', '权益介绍')->display(function ($introduce){
            return str_replace("\n", "<br />", $introduce);
        });
        $grid->column('sort', '排序')->editable();
        $grid->column('updated_at', '编辑时间');

        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools){
            $tools->disableBatchActions();
        });

        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableDelete();
            $actions->disableView();
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Agent::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->price('Price');
        $show->introduce('Introduce');
        $show->sort('Sort');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Agent);

        $form->text('name', '名称')->rules('required');
        $form->decimal('price', '价格')->rules('required');
        $form->rate('recommend_level_one', '一级推荐奖励')->rules('required');
        $form->rate('recommend_level_two', '二级推荐奖励')->rules('required');



        $form->textarea('introduce', '权益介绍')->help('一行一条')->rules('required');
        $form->number('sort', '排序')->default(0)->help('倒序排列');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->footer(function (Form\Footer $footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }
}
