<?php

namespace App\Admin\Controllers\Activity;

use App\Admin\Extensions\Refund;
use App\Models\Activity\Activity;
use App\Models\Activity\ActivityApply;
use App\Http\Controllers\Controller;
use App\Models\Activity\Specification;
use App\Models\User\UserBalance;
use App\Models\User\UserBalanceLog;
use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
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
            ->header('退款申请')
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
            ->header('退款申请')
            ->description('详情')
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
            ->header('退款申请')
            ->description('更新')
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
            ->header('退款申请')
            ->description('创建')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $activity = new Activity();
        $activityApplyModel = new ActivityApply();
        $grid = new Grid($activityApplyModel);
        $allowArr = [$activityApplyModel::REFUND_STATUS_NOT, $activityApplyModel::REFUND_STATUS_REFUNDABLE];
        $grid->model()->whereNotIn('refund_status', $allowArr)->whereHas('activity')->orderBy('created_at','desc');
        $grid->filter(function($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->equal('id','id');
//            $filter->like('activity.title','活动标题');
            $filter->like('name','联系人');
            $filter->like('phone','联系电话');
        });
        $grid->column('id','报名id')->sortable();
        $grid->column('uid','用户id');
        $grid->column('activity.id','活动id');
//        $grid->column('activity.title','活动标题');
        $grid->column('activity.type','活动类型')->using($activity->getType());
        $grid->column('name','联系人');
        $grid->column('phone','联系电话');
        $grid->column('company_name','工作单位');
        $grid->column('price','报名金额');
        $grid->column('order_no','订单编号');
        $grid->column('created_at','报名时间');
        $grid->column('pay_type','支付方式')->using($activityApplyModel->getPayTypeShow());
        $grid->column('refund_status','退款状态')->using($activityApplyModel->getRefundShow());
        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($activityApplyModel) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            // append一个操作
            $rowInfo = $actions->row;
            if(($rowInfo->price > 0) && ($rowInfo->refund_status == ActivityApply::REFUND_STATUS_PROCESSING)){
                $data = $activityApplyModel->getRefundData($actions->row);
                $actions->prepend(new Refund($data));
            }
        });
        $grid->disableCreateButton();//去掉新增按钮
        return $grid;
    }

    public function applyRefund(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            return response()->json(['message'=>'不存在的订单，请稍后重试']);
        }
        $orderModel = new ActivityApply();
        $detail = $orderModel->where('id', $id)->first();
        if (empty($detail)) {
            return response()->json(['message'=>'不存在的订单，请稍后重试']);
        }
        if ($detail->status != $orderModel::STATUS_COMPLETED || $detail->pay_status != $orderModel::PAY_STATUS_PAID || $detail->refund_status != $orderModel::REFUND_STATUS_PROCESSING) {
            return response()->json(['message'=>'该订单无法退款，请联系管理员']);
        }
        // 判断该订单的支付方式
        switch ($detail->pay_type) {
            case $orderModel::PAY_TYPE_WECHAT:
                // 生成退款订单号
                $refundNo = $orderModel::getAvailableRefundNo();
                $refundMoney = bcmul($detail->price,100);
                try{
                    $a = app('wechat_pay')->refund([
                        'out_trade_no' => $detail->order_no,
                        'total_fee' => $refundMoney,
                        'refund_fee' => $refundMoney,
                        'out_refund_no' => $refundNo,
                        'notify_url' => ngrok_url('payment.wechat.refund_notify'),
                    ]);
                    $detail->update([
                        'refund_no' => $refundNo,
                        'refund_status' => ActivityApply::REFUND_STATUS_SUCCESS,
                    ]);
                    return response()->json(['status' => 1, 'message' => '退款成功，已退还到微信']);
                } catch (\Exception $exception){
                    return response()->json(['status' => 0, 'message' => '退款失败，系统错误']);
                }
                break;
            case $orderModel::PAY_TYPE_BALANCE:
                $bool = $this->balanceRefundNotify($detail);
                if ($bool) {
                    return response()->json(['status' => 1, 'message' => '退款成功，已退还到余额']);
                } else {
                    return response()->json(['status' => 0, 'message' => '退款失败']);
                }
                break;
            default:
                return response()->json(['message'=>'未知订单支付方式, 请联系管理员']);
                break;
        }
    }

    public function balanceRefundNotify ($detail) {
        $orderModel = new ActivityApply();
        if ($detail->status != $orderModel::STATUS_COMPLETED || $detail->pay_status != $orderModel::PAY_STATUS_PAID || $detail->refund_status != $orderModel::REFUND_STATUS_PROCESSING) {
            return false;
        }
        if ($detail->pay_type != $orderModel::PAY_TYPE_BALANCE) {
            return false;
        }
        if (!($detail->price >0)) {
            return false;
        }
        try{
            DB::beginTransaction();
            $price = $detail->price;
            UserBalance::where('user_id', $detail->uid)->increment('money', $price);
            $userBalanceLog = new UserBalanceLog();
            $userBalanceLog::addLog($detail->uid, $userBalanceLog::LOG_TYPE_INCOME, $userBalanceLog::TYPE_ACTIVITY_REFUND, $price, '', $detail->aid, $detail->id);
            Specification::where('id', $detail->sid)->increment('remainder');
            $detail->update([
                'status' => $orderModel::STATUS_CANCEL,
                'refund_status' => $orderModel::REFUND_STATUS_SUCCESS,
                'refund_at' => Carbon::now()
            ]);
            DB::commit();
            return true;
        }catch (\Exception $exception){
            DB::rollBack();
            return false;
        }



    }

}
