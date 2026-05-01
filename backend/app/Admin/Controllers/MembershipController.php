<?php

namespace App\Admin\Controllers;

use App\Models\Membership;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MembershipController extends Controller
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
            ->header('会员认证')
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
        $grid = new Grid(new Membership);
        $grid->model()->with(['user', 'carte', 'association'])->latest();

        $grid->column('id', 'ID')->sortable();
        $grid->column('user.nickname', '微信昵称');
        $grid->column('carte.name', '名片姓名');
        $grid->column('carte.avatar', '名片头像')->image('', 20, 20);
        $grid->column('user.phone', '手机号');

        $grid->column('association.name', '所属协会');
        $grid->column('status_text', '状态');
        $grid->column('remark', '备注');

        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '操作时间');

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableTools();
        $grid->filter(function (Grid\Filter  $filter) {
            $filter->disableIdFilter();
            $filter->like('user.nickname', '微信昵称');
            $filter->like('carte.name', '名片姓名');
            $filter->like('user.phone', '手机号');
            $filter->like('association.name', '协会名称');
            $filter->equal('status', '状态')->select(Membership::STATUS_TEXT);
            $filter->between('created_at', '创建时间')->datetime();
        });

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();
            if ($this->row->status != 0) {
                $actions->disableEdit();
            }
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
        $show = new Show(Membership::findOrFail($id));

        $show->id('Id');
        $show->user_id('User id');
        $show->aid('Aid');
        $show->carte_id('Carte id');
        $show->status('Status');
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
        $form = new Form(new Membership);
        $options = Membership::STATUS_TEXT;
        unset($options[Membership::STATUS_UNREVIEWED]);

        $form->display('user.nickname', '微信昵称');
        $form->display('carte.name', '名片姓名');
        $form->display('user.phone', '手机号');
        $form->display('association.name', '所属协会');
        $form->display('status_text', '状态');

        $form->radio('status', '状态')->options($options);
        $form->textarea('remark', '备注');

        $form->disableViewCheck();
        $form->disableCreatingCheck();
        $form->disableEditingCheck();

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
