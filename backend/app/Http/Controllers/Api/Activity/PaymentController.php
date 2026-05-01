<?php

namespace App\Http\Controllers\Api\Activity;

use App\Events\UserMoneyEvent;
use App\Http\Requests\ApplyBalanceRequest;
use App\Http\Resources\PaymentResource;
use App\Jobs\ApplyOrderCommission;
use App\Models\Activity\Activity;
use App\Models\Activity\ActivityApply;
use App\Models\Activity\Specification;
use App\Models\Configure;
use App\Models\User;
use App\Models\User\UserAuth;
use App\Models\User\UserBalance;
use App\Models\User\UserBalanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    // 微信支付
    public function wechatPay(Request $request) {
        $id = $request->get('id');
        $user_id = $request->user()->id;
        $orderModel = new ActivityApply();
        // 检查订单id是否属于当前用户
        $where['id'] = $id;
        $where['uid'] = $user_id;
        $where['pay_status'] = $orderModel::PAY_STATUS_PENDING;
        $orderInfo = $orderModel->where($where)->first();
        if(!$orderInfo){
            abort(403,'当前状态权限不足');
        }

        $userAuthWhere['user_id'] = $user_id;
        $userAuthWhere['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
        $userOpenId = UserAuth::where($userAuthWhere)->value('identifier');
        $body = '活动报名订单支付';
        $money = bcmul($orderInfo->price,100);
        if (!(ceil($money) > 0)) {
            abort(403,'支付失败，请稍后重试');
        }
        $order = [
            'out_trade_no' => $orderInfo->order_no,
            'body' => $body,
            'total_fee' => $money,
            'openid' => $userOpenId,
            'notify_url' => route('apply.wechat.notify'),
        ];
        $pay = app('wechat_pay')->miniapp($order);
        return new PaymentResource($pay);
    }

    // 余额支付
    public function balancePay(ApplyBalanceRequest $request) {
        $id = $request->get('id');
        $user = $request->user();
        // 检查订单id是否属于当前用户
        $where['id'] = $id;
        $where['uid'] = $user->id;
        $where['pay_status'] = ActivityApply::PAY_STATUS_PENDING;
        $where['status'] = ActivityApply::STATUS_UNDONE;
        $applyResult = ActivityApply::query()->where($where)->first();
        if(!$applyResult){
            abort(403,'当前订单不存在');
        }
        $activityModel = new Activity();
        $activityResult = $activityModel->where('id', $applyResult->aid)->first();
        if (empty($activityResult)) {
            abort(403,'报名活动不存在');
        }
        // 计算结算剩余时间
        $configure = new Configure();
        $settle_time = $configure->getConfigure('SETTLE_TIME');
        $remaining_time = strtotime($activityResult->activity_time) + $settle_time*3600*24 - time();
        $price = $applyResult->price;
        if(bccomp($price, 0.00, 2) > 0){
            DB::beginTransaction();
                $userBalancce = $user->balance;
                if(!$userBalancce){
                    UserBalance::addDefaultData($user->id);
                }
                if(bccomp($userBalancce->money, $price, 2) === -1){
                    abort(403, '余额不足');
                }
                if(bccomp($price, 0.00, 2) === 1){
                    // 会员减少余额
                    $userBalancce->decrement('money', $price);
                    // 修改秘钥
                    event(new UserMoneyEvent($user));
                }
                try{
                    // 报名成功 , 规格剩余数减1
                    Specification::where(['id' => $applyResult['sid'], 'aid' => $applyResult['aid']])->decrement('remainder');
                    $this->userBalanceLogs($user, $price);
                    $applyResult->payment_no = createOrderNo();
                    $applyResult->paid_at = Carbon::now();
                    $applyResult->status = ActivityApply::STATUS_COMPLETED;
                    $applyResult->pay_status = ActivityApply::PAY_STATUS_PAID;
                    $applyResult->pay_type = ActivityApply::PAY_TYPE_BALANCE;
                    $applyResult->save();
                    // 该活动报名完成后，将用户报名该活动未完成的订单状态全部改为取消报名状态
                    $this->changeOtherOrder($applyResult);
                    dispatch(new ApplyOrderCommission($applyResult, $remaining_time));
                    DB::commit();
                    return new PaymentResource($user);
                }catch (\Exception $e) {
                    DB:: rollBack();
                    Log::error($e->getMessage());
                    abort(403, '系统出错');
                }

        }else{
            abort(403, '非法操作');
        }
    }

    protected function changeOtherOrder($applyResult) {
        $applyModel = new ActivityApply();
        $where['uid'] = $applyResult['uid'];
        $where['aid'] = $applyResult['aid'];
        $where['status'] = $applyModel::STATUS_UNDONE;
        $where['pay_status'] = $applyModel::PAY_STATUS_PENDING;
        $applyModel->where($where)->update([
            'pay_status' => $applyModel::PAY_STATUS_TIMEOUT,
            'refund_status' => $applyModel::REFUND_STATUS_NOT,
            'status' => $applyModel::STATUS_CANCEL,
        ]);
    }

    // 会员流水
    protected function userBalanceLogs(User $user, $price){
        return UserBalanceLog::addLog($user->id, UserBalanceLog::LOG_TYPE_PAY, UserBalanceLog::TYPE_ACTIVITY_FEE, $price);
    }

    // 微信异步回调
    public function wechatPayNotify(){
        DB::beginTransaction();
        try{
            // 校验回调参数是否正确
            $data = app('wechat_pay')->verify();

            $applyResult = ActivityApply::query()->where('payment_no', $data->out_trade_no)->first();
            if(empty($applyResult)){
                return 'fail';
            }
            $activityModel = new Activity();
            $activityResult = $activityModel->where('id', $applyResult->aid)->first();
            // 计算结算剩余时间
            $configure = new Configure();
            $settle_time = $configure->getConfigure('SETTLE_TIME');
            $remaining_time = strtotime($activityResult->activity_time) + $settle_time*3600*24 - time();
            // 检查是否已报名
            if($applyResult->status === ActivityApply::STATUS_COMPLETED){
                return app('wechat_pay')->success();
            }
            // 检查是否已支付
            if($applyResult->pay_status === ActivityApply::PAY_STATUS_PAID){
                return app('wechat_pay')->success();
            }
            // 报名成功 , 规格剩余数减1
            Specification::where(['id' => $applyResult['sid'], 'aid' => $applyResult['aid']])->decrement('remainder');
            // 修改记录状态
            $applyResult->payment_no = $data->transaction_id;
            $applyResult->paid_at = Carbon::now();
            $applyResult->status = ActivityApply::STATUS_COMPLETED;
            $applyResult->pay_status = ActivityApply::PAY_STATUS_PAID;
            $applyResult->pay_type = ActivityApply::PAY_TYPE_WECHAT;
            $applyResult->save();
            // 该活动报名完成后，将用户报名该活动未完成的订单状态全部改为取消报名状态
            $this->changeOtherOrder($applyResult);
            dispatch(new ApplyOrderCommission($applyResult, $remaining_time));
            DB::commit();
            return app('wechat_pay')->success();
        }catch (\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            return 'fail';
        }
    }


    public function wechatRefundNotify(Request $request)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        $data = app('wechat_pay')->verify(null, true);

        $applyModel = new ActivityApply();
        // 没有找到对应的订单，原则上不可能发生，保证代码健壮性
        if(!$order = $applyModel::where('order_no', $data['out_trade_no'])->first()) {
            // 检查是否是报名订单，根据订单号查询订单支付信息，获取 attach 参数是否存在，且为 strtoupper('discover_apply');
            $discoverApplyInfo = $this->discoverApplyWechatRefundNotify($data);
            return $discoverApplyInfo;
        }

        if ($data['refund_status'] === 'SUCCESS') {
            // 退款成功，将订单退款状态改成退款成功

            // 将对应的规格人数还原
            Specification::where('id', $order->sid)->increment('remainder');
            $order->update([
                'status' => $applyModel::STATUS_CANCEL,
                'refund_status' => $applyModel::REFUND_STATUS_SUCCESS,
                'refund_at' => Carbon::now()
            ]);
        } else {
            // 退款失败，将具体状态存入 extra 字段，并表退款状态改成失败
            $extra = $order->extra;
            $extra['refund_failed_code'] = $data['refund_status'];
            $order->update([
                'refund_status' => $applyModel::REFUND_STATUS_FAILED,
                'extra' => $extra
            ]);
        }

        return app('wechat_pay')->success();
    }



    public function discoverApplyWechatRefundNotify($data)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';

        // 查询订单
        $orderConfig = [
            'out_trade_no' => $data->out_trade_no,
        ];
        $orderOutInfo = app('wechat_pay')->find($orderConfig);
        if(isset($orderOutInfo->attach)){
            $responseAttach = strtoupper($orderOutInfo->attach);
            $attach = strtoupper('discover_apply');
            if($responseAttach != $attach){
                return $failXml;
            }
        }
        $applyModel = new ActivityApply();
        $order = $applyModel::where('order_no', $data->out_trade_no)->first();
        // 没有找到对应的订单，原则上不可能发生，保证代码健壮性
        if(!$order) {
            // 检查是否是报名订单，根据订单号查询订单支付信息，获取 attach 参数是否存在，且为 strtoupper('discover_apply');
            return $failXml;
        }

        if ($data['refund_status'] === 'SUCCESS') {
            // 退款成功，将订单退款状态改成退款成功

            // 将对应的规格人数还原
            Specification::where('id', $order->sid)->increment('remainder');
            $order->update([
                'status' => $applyModel::STATUS_CANCEL,
                'refund_status' => $applyModel::REFUND_STATUS_SUCCESS,
                'refund_at' => Carbon::now()
            ]);

        } else {
            // 退款失败，将具体状态存入 extra 字段，并表退款状态改成失败
            $extra = $order->extra;
            $extra['refund_failed_code'] = $data['refund_status'];
            $order->update([
                'refund_status' => $applyModel::REFUND_STATUS_FAILED,
                'extra' => $extra
            ]);
        }

        return app('wechat_pay')->success();
    }
}
