<?php

namespace App\Admin\Controllers;

use App\Models\CompanyCardLog;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CompanyCardLogController extends Controller
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
            ->header('企业会员开通记录')
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
        $grid = new Grid(new CompanyCardLog);
        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', 'ID')->sortable();
        $grid->column('user.nickname', '微信昵称');
        $grid->column('user.carte', '名片姓名')->display(function($carte){
            return $carte['name'] ?? '未填写';
        });
        $grid->column('money', '支付金额(元)');
        $grid->column('order_no', '订单号');
        $grid->column('pay_type', '支付类型')->display(function($pay_type){
            return CompanyCardLog::getPayments($pay_type);
        });
        $grid->column('is_pay', '是否支付')->display(function($is_pay){
            return $is_pay === CompanyCardLog::PAY_PAID ? CompanyCardLog::getPayStatus(CompanyCardLog::PAY_PAID) : CompanyCardLog::getPayStatus(CompanyCardLog::PAY_UNPAID);
        });
        $grid->column('paid_at', '支付时间')->display(function($paid_at){
            return $paid_at ? $paid_at : '-';
        });
        $grid->column('created_at', '下单时间');

        $grid->disableExport();
        $grid->disableCreateButton();

        $grid->tools(function(Grid\Tools $tools){
            $tools->batch(function(Grid\Tools\BatchActions $actions){
                $actions->disableDelete();
            });
        });

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->like('user.nickname', '微信昵称');
            $filter->like('order_no', '订单编号');
            $filter->equal('is_pay', '是否支付')->select(CompanyCardLog::getPayStatus());
            $filter->between('paid_at', '支付时间')->datetime();
            $filter->between('created_at', '下单时间')->datetime();
        });
        $grid->disableActions();
//        $grid->actions(function (Grid\Displayers\Actions $actions){
//            $actions->disableEdit();
//            $actions->disableView();
//            $actions->disableDelete();
//        });


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
        $show = new Show(CompanyCardLog::findOrFail($id));

        $show->id('Id');
        $show->user_id('User id');
        $show->money('Money');
        $show->order_no('Order no');
        $show->payment_no('Payment no');
        $show->remark('Remark');
        $show->pay_type('Pay type');
        $show->is_pay('Is pay');
        $show->paid_at('Paid at');
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
        $form = new Form(new CompanyCardLog);

        $form->number('user_id', 'User id');
        $form->decimal('money', 'Money');
        $form->text('order_no', 'Order no');
        $form->text('payment_no', 'Payment no');
        $form->text('remark', 'Remark');
        $form->switch('pay_type', 'Pay type')->default(1);
        $form->switch('is_pay', 'Is pay');
        $form->datetime('paid_at', 'Paid at')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
