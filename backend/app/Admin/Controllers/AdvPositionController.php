<?php

namespace App\Admin\Controllers;

use App\Models\AdvPosition;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AdvPositionController extends Controller
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
            ->header('广告位置管理')
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
            ->header('广告位置管理')
            ->description('详细')
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
            ->header('广告位置管理')
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
            ->header('广告位置管理')
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
        $grid = new Grid(new AdvPosition);

        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', 'ID')->sortable();
        $grid->column('name', '位置名称');
        $grid->column('flag', '位置标识');
        $grid->column('describe', '位置描述');
        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '编辑时间');

        $grid->disableExport();
        $grid->disableFilter();
        $grid->actions(function (Grid\Displayers\Actions $actions){
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
        $show = new Show(AdvPosition::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->describe('Describe');
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
        $form = new Form(new AdvPosition);

        $form->text('name', '位置名称')->rules('required');
        $form->text('flag', '位置标识')->rules(function ($form){
            if ($id = $form->model()->id) {
                return 'required|unique:adv_positions,flag,' . $id;
            }else{
                return 'required|unique:adv_positions,flag';
            }
        });
        $form->textarea('describe', '描述');

        return $form;
    }
}
