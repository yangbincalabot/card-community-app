<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserCouponsResource;
use App\Models\CouponCode;
use App\Models\User\UserCoupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponsController extends Controller
{
    public function getCoupons(Request $request,UserCoupon $userCoupon,CouponCode $couponCode){
        $id = $request->get('id');
        $user_id = $request->user()->id;
        // 检查该优惠券id是否可以被领取
        $couponCodeWhere['id'] = $id;
        $couponCodeWhere['enabled'] = true;
        $couponCodeInfo = $couponCode->where($couponCodeWhere)->first();
        if(empty($couponCodeInfo)){
            abort(403,'优惠券不存在');
        }
        // 检查优惠券剩余数量是否可以领取
        $surplusCount = bcsub($couponCodeInfo->total,$couponCodeInfo->used);
        if($surplusCount <= 0){
            abort(403,'当前优惠券已被领取完，不可领取');
        }
        // 检查用户是否已经领取过该优惠券
        $userCouponWhere['user_id'] = $user_id;
        $userCouponWhere['coupon_id'] = $id;
        $userCouponInfo = $userCoupon->where($userCouponWhere)->first();
        if($userCouponInfo){
            abort(403,'你已经领取过这张优惠券了');
        }
        $createData['user_id'] = $user_id;
        $createData['coupon_id'] = $id;
        $createData['used_at'] = Carbon::now()->toDateTimeString();
        UserCoupon::query()->create($createData);
        // 增加优惠券的领取量
        CouponCode::where('id',$id)->whereRaw('used < total')->increment('used');
        return new UserCouponsResource(collect([]));
    }

    // 获取用户优惠券列表
    public function index(Request $request){
        $user_id = $request->user()->id;
        $where['user_id'] = $user_id;
        $where['status'] = UserCoupon::STATUS_NOT_USE;
        $coupons = UserCoupon::where($where)->with('coupons')->has('coupons')->get();
        return new UserCouponsResource($coupons);
    }
}
