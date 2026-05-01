<?php

namespace App\Admin\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OrderService;
use App\Services\SalesCommissionsService;
use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfflineOrderController extends Controller
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
            ->header('订单列表')
            ->body($this->grid());
    }

    public function show(Order $order, Content $content)
    {
        return $content
            ->header('查看订单')
            // body 方法可以接受 Laravel 的视图作为参数
            ->body(view('admin.orders.offline_show', ['order' => $order]));
    }

    public function confirmOfflinePaid(Order $order, Request $request,SalesCommissionsService $salesCommissionsService){
        // 判断当前订单是否为线下支付订单
        if ($order->payment_method != Order::PAYMENT_METHOD_OFFLINE_PAY) {
            throw new InvalidRequestException('该订单类型不可执行当前操作');
        }
        // 判断订单状态是否为待确认线下收款
        if ($order->ship_status != Order::SHIP_STATUS_OFFLINE_PENDING) {
            throw new InvalidRequestException('该订单状态不可执行当前操作');
        }

        // Laravel 5.5 之后 validate 方法可以返回校验过的值
        $this->validate($request, [
            'bank_name' => ['required'],
            'bank_trading_number'      => ['required'],
        ], [], [
            'bank_name' => '银行名称',
            'bank_trading_number'      => '银行流水号',
        ]);

        // 将订单状态改为已支付，并存入线下支付交易信息
        // 将线下支付交易信息放到订单的 extra 字段中
        $extra = $order->extra ?: [];
        $extra['bank_name'] = $request->input('bank_name');
        $extra['bank_trading_number'] = $request->input('bank_trading_number');


        DB::transaction(function () use ($order,$extra,$salesCommissionsService) {
            $order->update([
                'payment_no' => $extra['bank_trading_number'],
                'ship_status' => Order::SHIP_STATUS_DELIVERING,
                // 我们在 Order 模型的 $casts 属性里指明了 ship_data 是一个数组
                // 因此这里可以直接把数组传过去
                'extra'   => $extra,
                'confirm_offline_pay_at' => Carbon::now()->toDateTimeString()
            ]);
            $user = User::where('id',$order->user_id)->first();

            $salesCommissionsService->freeze($user,$order->total_amount,$order->id);
        });

        $this->afterPaid($order);
        // 返回上一页
        return redirect()->back();
    }

    public function confirmOfflineRefund(Order $order, Request $request,SalesCommissionsService $salesCommissionsService){
        // 判断当前订单是否为线下支付订单
        if ($order->payment_method != Order::PAYMENT_METHOD_OFFLINE_PAY) {
            throw new InvalidRequestException('该订单类型不可执行当前操作');
        }
        // 判断订单状态是否为待确认线下退款
        if ($order->refund_status != Order::REFUND_STATUS_PROCESSING) {
            throw new InvalidRequestException('该订单状态不可执行当前操作');
        }

        // Laravel 5.5 之后 validate 方法可以返回校验过的值
        $this->validate($request, [
            'bank_name' => ['required'],
            'bank_trading_number'      => ['required'],
        ], [], [
            'bank_name' => '银行名称',
            'bank_trading_number'      => '银行流水号',
        ]);

        // 将订单状态改为已支付，并存入线下支付交易信息
        // 将线下支付交易信息放到订单的 extra 字段中
        $extra = $order->extra ?: [];
        $extra['refund_bank_name'] = $request->input('bank_name');
        $extra['refund_bank_trading_number'] = $request->input('bank_trading_number');


        DB::transaction(function () use ($order,$extra,$salesCommissionsService) {
            $order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
                // 我们在 Order 模型的 $casts 属性里指明了 ship_data 是一个数组
                // 因此这里可以直接把数组传过去
                'extra'   => $extra,
            ]);
            $user = User::where('id',$order->user_id)->first();

            $salesCommissionsService->cancel($user,$order->total_amount,$order->id);
        });
        // 返回上一页
        return redirect()->back();
    }

    public function ship(Order $order, Request $request)
    {
        // 判断当前订单是否已支付
        if (!$order->confirm_offline_pay_at) {
            throw new InvalidRequestException('该订单未付款');
        }
        // 判断当前订单发货状态是否为未发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERING) {
            throw new InvalidRequestException('该订单已发货');
        }
        // 众筹订单只有在众筹成功之后发货
        if ($order->type === Order::TYPE_CROWDFUNDING &&
            $order->items[0]->product->crowdfunding->status !== CrowdfundingProduct::STATUS_SUCCESS) {
            throw new InvalidRequestException('众筹订单只能在众筹成功之后发货');
        }
        // Laravel 5.5 之后 validate 方法可以返回校验过的值
        $data = $this->validate($request, [
            'express_company' => ['required'],
            'express_no'      => ['required'],
        ], [], [
            'express_company' => '物流公司',
            'express_no'      => '物流单号',
        ]);
        // 将订单发货状态改为已发货，并存入物流信息
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            // 我们在 Order 模型的 $casts 属性里指明了 ship_data 是一个数组
            // 因此这里可以直接把数组传过去
            'ship_data'   => $data,
        ]);

        // 返回上一页
        return redirect()->back();
    }

    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }

    public function handleRefund(Order $order, HandleRefundRequest $request, OrderService $orderService)
    {
        // 判断订单状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_APPLIED) {
            throw new InvalidRequestException('订单状态不正确');
        }
        // 是否同意退款
        if ($request->input('agree')) {
            // 清空拒绝退款理由
            $extra = $order->extra ?: [];
            unset($extra['refund_disagree_reason']);
            $order->update([
                'extra' => $extra,
            ]);
            // 改为调用封装的方法
            $orderService->refundOrder($order);
        } else {
            // 将拒绝退款理由放到订单的 extra 字段中
            $extra = $order->extra ?: [];
            $extra['refund_disagree_reason'] = $request->input('reason');
            // 将订单的退款状态改为未退款
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra'         => $extra,
            ]);
        }

        return $order;
    }

    protected function grid()
    {
        $grid = new Grid(new Order);

        // 只展示已支付的订单，并且默认按支付时间倒序排序
        // 只展示选择了线下支付的订单
        // 新增一个确认线下支付成功的按钮
//        $grid->model()->where('ship_status',Order::SHIP_STATUS_OFFLINE_PENDING)->orderBy('updated_at', 'asc');

        // 如果角色是只发货的角色，就只查询待发货的订单
        $where = [];
        if(Admin::user()->isRole('delivering')){
            $where['ship_status'] = Order::SHIP_STATUS_DELIVERING;
            $where['refund_status'] = Order::REFUND_STATUS_PENDING;
        }
        $grid->model()->where('payment_method',Order::PAYMENT_METHOD_OFFLINE_PAY)->where($where)->orderBy('updated_at', 'desc');

        $grid->no('订单流水号');
        // 展示关联关系的字段时，使用 column 方法
        $grid->column('user_id', '买家编号');
        $grid->column('user.nickname', '买家昵称');
        $grid->total_amount('总金额')->sortable();
        $grid->confirm_offline_pay_at('确认收款时间')->sortable();
        $grid->ship_status('物流')->display(function($value) {
            return Order::$shipStatusMap[$value];
        });
        $grid->refund_status('退款状态')->display(function($value) {
            return Order::$refundStatusMap[$value];
        });
        // 禁用创建按钮，后台不需要创建订单
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 禁用删除和编辑按钮
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('no', '订单号');
            $filter->equal('ship_status','订单状态')->select(Order::$shipStatusMap);
            $filter->equal('refund_status','售后状态')->select(Order::$refundStatusMap);
            $filter->between('created_at', '下单时间')->datetime();
            $filter->between('confirm_offline_pay_at', '确认线下收款时间')->datetime();
        });
        return $grid;
    }
}
