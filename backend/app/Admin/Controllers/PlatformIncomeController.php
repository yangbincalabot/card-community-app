<?php

namespace App\Admin\Controllers;

use App\Models\PlatformIncome;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class PlatformIncomeController extends Controller
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
            ->header('平台收益')
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
            ->header('Detail')
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
            ->header('Edit')
            ->description('description')
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
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PlatformIncome);
        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', 'ID')->sortable();
        $grid->column('user.nickname', '微信昵称');
        $grid->column('user.carte', '名片姓名')->display(function($carte){
            return $carte['name'] ?? '未填写';
        });
        $grid->column('type', '收益类型')->display(function($type){
            return PlatformIncome::getTypes($type);
        });
        $grid->column('money', '金额');
//        $grid->column('info_id', '');
        $grid->column('remark', '说明');
        $grid->column('created_at', '时间');

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->tools(function(Grid\Tools $tools){
            $tools->batch(function(Grid\Tools\BatchActions $actions){
                $actions->disableDelete();
            });
        });
        $grid->disableActions();

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->like('user.nickname', '微信昵称');
            $filter->equal('type', '类型')->select(PlatformIncome::getTypes());
            $filter->between('created_at', '时间')->datetime();
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
        $show = new Show(PlatformIncome::findOrFail($id));

        $show->id('Id');
        $show->user_id('User id');
        $show->type('Type');
        $show->money('Money');
        $show->info_id('Info id');
        $show->remark('Remark');
        $show->deleted_at('Deleted at');
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
        $form = new Form(new PlatformIncome);

        $form->number('user_id', 'User id');
        $form->switch('type', 'Type')->default(1);
        $form->decimal('money', 'Money')->default(0.00);
        $form->number('info_id', 'Info id');
        $form->text('remark', 'Remark');

        return $form;
    }
}
