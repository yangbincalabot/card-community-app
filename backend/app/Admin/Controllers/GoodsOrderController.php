<?php

namespace App\Admin\Controllers;

use App\Models\GoodsOrder;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class GoodsOrderController extends Controller
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
            ->header('购买记录')
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
        $grid = new Grid(new GoodsOrder);

        $grid->model()->where('is_pay', GoodsOrder::IS_PAY_TRUE)->orderBy('id', 'desc');

        $grid->column('id', 'ID')->sortable();
        $grid->column('order_sm', '订单编号');
        $grid->column('goods.title', '商品标题');
        $grid->column('user.nickname', '微信昵称');
        $grid->column('price', '价格');
        $grid->column('is_pay', '支付状态')->display(function ($is_pay){
            return $is_pay === GoodsOrder::IS_PAY_TRUE ? '已支付' : '未支付';
        });


        $grid->column('payed_at', '支付时间');
        $grid->column('created_at', '下单时间');



        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->like('goods.title', '标题');
            $filter->like('user.nickname', '微信昵称');
            $filter->between('created_at', '下单时间')->datetime();
            $filter->between('payed_at', '支付时间')->datetime();
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
        $show = new Show(GoodsOrder::findOrFail($id));

        $show->id('Id');
        $show->goods_id('Goods id');
        $show->user_id('User id');
        $show->price('Price');
        $show->is_pay('Is pay');
        $show->payed_at('Payed at');
        $show->order_sm('Order sm');
        $show->pay_sm('Pay sm');
        $show->num('Num');
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
        $form = new Form(new GoodsOrder);

        $form->number('goods_id', 'Goods id');
        $form->number('user_id', 'User id');
        $form->decimal('price', 'Price')->default(0.00);
        $form->switch('is_pay', 'Is pay');
        $form->datetime('payed_at', 'Payed at')->default(date('Y-m-d H:i:s'));
        $form->text('order_sm', 'Order sm');
        $form->text('pay_sm', 'Pay sm');
        $form->number('num', 'Num')->default(1);

        return $form;
    }
}
