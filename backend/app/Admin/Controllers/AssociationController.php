<?php

namespace App\Admin\Controllers;

use App\Models\Association;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AssociationController extends Controller
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
            ->header('协会列表')
            ->description('小程序用户创建的协会列表')
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
            ->header('审核')
            ->description('协会审核')
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
        $grid = new Grid(new Association);
        $grid->model()->where('user_id', '>', 0)->orderBy('updated_at', 'desc');



        $grid->column('id', 'ID')->sortable();

        $grid->column('user.nickname', '昵称');
        $grid->column('company.company_name', '企业名称');
        $grid->column('name', '协会名称');
        $grid->column('status_text', '状态');
        $grid->column('remark', '备注');
        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '操作时间');

        $grid->disableCreateButton();

        $grid->disableExport();
        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->like('user.nickname', '昵称');
            $filter->like('company.company_name', '公司名称');
            $filter->equal('status', '状态')->select(Association::STATUS_TEXT);
        });

        $grid->disableRowSelector();

        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();
//            $actions->disableDelete();
            if ($this->row->status !== Association::STATUS_NOT_REVIEWED){
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
        $show = new Show(Association::findOrFail($id));

        $show->id('Id');
        $show->user_id('User id');
        $show->name('Name');
        $show->image('Image');
        $show->status('Status');
        $show->remark('Remark');
        $show->desc('Desc');
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
        $form = new Form(new Association);

        $id = isset(request()->route()->parameters()['association']) ? request()->route()->parameters()['association'] : null;
        $form->hidden('user_id');
        $form->display('user.nickname', '昵称');
        $form->display('company.company_name', '公司名称');
        $form->display('name', '协会名称');
        $form->image('image', '图片')->disable();
        $form->multipleImage('images', '封面图')->disable();
        $form->textarea('desc', '协会描述')->disable();
        $form->radio('status', '状态')->options([
            Association::STATUS_FAILURE => '不通过',
            Association::STATUS_SUCCESS => '通过',
        ]);
        $form->textarea('remark', '备注');

        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools){
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->saving(function (Form $form) use ($id) {
            $status = request()->get('status');
            if ($status == Association::STATUS_SUCCESS && $id) {
                $uid = $form->user_id;
                User::query()->where('id', $uid)->update(['aid' => $id]);
            }
        });

        return $form;
    }

    public function destroy($id)
    {
        // 删除协会后，解除用户和协会之间的关系
        User::query()->where('aid', $id)->update(['aid' => 0]);
        return $this->form()->destroy($id);
    }
}
