<?php

namespace App\Admin\Controllers;

use App\Models\Goods;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class GoodsController extends Controller
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
            ->header('商品列表')
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
        $grid = new Grid(new Goods);

        $grid->model()->orderBy('id', 'desc');


        $grid->column('id', 'ID')->sortable();
        $grid->column('user.nickname', '微信昵称');
        $grid->column('company.company_name', '公司名称');
        $grid->column('title', '标题');
        $grid->column('price', '价格');
        $grid->column('image', '封面')->image(60, 60);
        $grid->column('sales', '销量');
        $grid->column('views', '浏览量');
        $grid->column('is_show', '是否上架');
        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '编辑时间');

        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->like('title', '标题');
            $filter->like('user.nickname', '微信昵称');
            $filter->like('user.company_name', '公司名称');
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
        $show = new Show(Goods::findOrFail($id));

        $show->id('Id');
        $show->user_id('User id');
        $show->cid('Cid');
        $show->title('Title');
        $show->price('Price');
        $show->image('Image');
        $show->images('Images');
        $show->content('Content');
        $show->sales('Sales');
        $show->views('Views');
        $show->is_show('Is show');
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
        $form = new Form(new Goods);

        $form->number('user_id', 'User id');
        $form->number('cid', 'Cid');
        $form->text('title', 'Title');
        $form->decimal('price', 'Price')->default(0.00);
        $form->image('image', 'Image');
        $form->text('images', 'Images');
        $form->textarea('content', 'Content');
        $form->number('sales', 'Sales');
        $form->number('views', 'Views');
        $form->switch('is_show', 'Is show')->default(1);

        return $form;
    }
}
