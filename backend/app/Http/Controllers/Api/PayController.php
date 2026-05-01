<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PaymentResource;
use App\Models\Activity\ActivityApply;
use App\Models\Order;
use App\Models\User\UserAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayController extends Controller
{
    //

    public function offlineConfirmPay(Request $request){
        // 检查订单是否在待支付的状态
        // 将订单状态改为线下支付成功，待确认
    }


    public function weChatMiNiPay(Order $order,Request $request){
        $id = $request->get('id');
        $type = $request->get('type','product');
        if(!in_array($type,['discover_apply','product'])){
            abort(403,'请求参数异常');
        }
        if($type == 'discover_apply'){
            return $this->discoverApplyWeChatMiNiPay($request);
        }
        $user_id = $request->user()->id;
        // 检查订单id是否属于当前用户
        $where['id'] = $id;
        $where['user_id'] = $user_id;
        $where['ship_status'] = Order::SHIP_STATUS_PENDING;
        $orderInfo = $order->where($where)->first();
        if(!$orderInfo){
            abort(403,'当前状态权限不足');
        }
        if($orderInfo->closed){
            abort(403,'订单长时间未支付，已自动关闭，请重新下单');
        }
        $userAuthWhere['user_id'] = $user_id;
        $userAuthWhere['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
        $userOpenId = UserAuth::where($userAuthWhere)->value('identifier');
        $body = 'DueLope';
        if($type == 'product'){
            $body .= '-商品订单支付';
        }
        $money = bcmul($orderInfo->total_amount,100);
//        $money = 1;
        // 检查订单是否在待支付的状态
        // 将订单状态改为线下支付成功，待确认

        $order = [
            'out_trade_no' => $orderInfo->no,
            'body' => $body,
            'total_fee' => $money,
            'openid' => $userOpenId,
        ];

        $pay = app('wechat_pay')->miniapp($order);
        return new PaymentResource($pay);
    }

    public function discoverApplyWeChatMiNiPay(Request $request){
        $id = $request->get('id');
        $user_id = $request->user()->id;
        $orderModel = new ActivityApply();
        // 检查订单id是否属于当前用户
        $where['id'] = $id;
        $where['user_id'] = $user_id;
        $where['status'] = $orderModel::STATUS_PENDING;
        $orderInfo = $orderModel->where($where)->first();
        if(!$orderInfo){
            abort(403,'当前状态权限不足');
        }

        $userAuthWhere['user_id'] = $user_id;
        $userAuthWhere['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
        $userOpenId = UserAuth::where($userAuthWhere)->value('identifier');
        $body = 'DueLope-活动报名订单支付';
//        $money = bcmul($orderInfo->total_amount,100);
        $money = 1;
        // 检查订单是否在待支付的状态
        // 将订单状态改为线下支付成功，待确认

        $attach = strtoupper('discover_apply');
        $order = [
            'out_trade_no' => $orderInfo->order_no,
            'body' => $body,
            'total_fee' => $money,
            'openid' => $userOpenId,
            'attach' => $attach
        ];
        $pay = app('wechat_pay')->miniapp($order);
        return new PaymentResource($pay);
    }
}
