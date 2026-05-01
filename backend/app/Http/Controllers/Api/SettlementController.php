<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SettlementResource;
use App\Models\CartItem;
use App\Models\CouponCode;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\User\UserCoupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettlementController extends Controller
{
    //
    public function index(Request $request,CouponCode $couponCode)
    {
        $user_id = $request->user()->id;
        // 获取提交的用户优惠券id（可以不提交）
        $user_coupon_id = $request->get('user_coupon_id','');
        $sku_ids_arr = $request->get('sku_ids');
        $sku_ids = array_column($sku_ids_arr,'sku_id');


        // 获取会员的上一次使用的收货地址，如果没有收货地址，则获取该会员第一条收货地址
        $addressWhere = [];
        $address_id = $request->get('address_id','');
        if($address_id != ''){
            $addressWhere['id'] = $address_id;
        }
        $addresses = $request->user()->addresses()->where($addressWhere)->orderBy('last_used_at', 'desc')->first();

        // 获取结算的商品信息
        $cartItems = $request->user()->cartItems()->whereIn('product_sku_id',$sku_ids)->with(['productSku.product'])->get();

        // 获取商品总数量
        $totalAmount = $cartItems->pluck('amount')->sum();
        // 计算总金额
        $totalPrice = 0;
        $cart_items_arr = [];
        foreach ($cartItems->toArray() as $item){
            $totalPrice = bcadd($totalPrice,bcmul($item['amount'],$item['product_sku']['product']['exclusive_price'],2),2);
            $cart_items_info['sku_id'] = $item['product_sku_id'];
            $cart_items_info['amount'] = $item['amount'];
            $cart_items_arr[] = $cart_items_info;
            unset($cart_items_info);
        }

        // 获取用户已有且当前订单可以使用的优惠券
        $userCouponWhere['user_id'] = $user_id;
        $userCouponWhere['status'] = UserCoupon::STATUS_NOT_USE;
        $userCoupons = UserCoupon::where($userCouponWhere)->with('coupons')->whereHas(
            'coupons', function ($query) use ($totalPrice) {
                $query->where('min_amount','<=', $totalPrice);
            }
        )->get();

        $currentCheckedCouponInfo = '';
        if ($user_coupon_id) {
            $currentCheckedCouponInfo = $userCoupons->where('id',$user_coupon_id)->first();
            // 把订单金额修改为优惠后的金额
            $totalPrice = $currentCheckedCouponInfo->getAdjustedPrice($totalPrice);
        }


        $data['address'] = $addresses;
        $data['cart_items'] = $cartItems;
        $data['total_amount'] = $totalAmount;
        $data['total_price'] = $totalPrice;
        $data['cart_items_arr'] = $cart_items_arr;
        $data['user_coupons'] = $userCoupons;
        $data['user_coupon_info'] = $currentCheckedCouponInfo;

        return new SettlementResource(collect($data));
    }

    public function buyNow(Request $request,CouponCode $couponCode)
    {
        $user_id = $request->user()->id;
        // 获取提交的用户优惠券id（可以不提交）
        $user_coupon_id = $request->get('user_coupon_id','');
        $sku_ids_arr = $request->get('sku_ids');
        $sku_ids = $sku_ids_arr['sku_id'];
        $totalAmount = $sku_ids_arr['amount'];


        // 获取会员的上一次使用的收货地址，如果没有收货地址，则获取该会员第一条收货地址
        $addressWhere = [];
        $address_id = $request->get('address_id','');
        if($address_id != ''){
            $addressWhere['id'] = $address_id;
        }
        $addresses = $request->user()->addresses()->where($addressWhere)->orderBy('last_used_at', 'desc')->first();

        // 获取结算的商品信息
        $cartItems = ProductSku::where('id',$sku_ids)->with(['product'])->get();

        // 计算总金额
        $totalPrice = 0;
        $cart_items_arr = [];
        foreach ($cartItems->toArray() as $item){
            $totalPrice = bcadd($totalPrice,bcmul(1,$item['product']['exclusive_price'],2),2);
            $cart_items_info['sku_id'] = $item['id'];
            $cart_items_info['amount'] = 1;
            $cart_items_arr[] = $cart_items_info;
            unset($cart_items_info);
        }

        // 获取用户已有且当前订单可以使用的优惠券
        $userCouponWhere['user_id'] = $user_id;
        $userCouponWhere['status'] = UserCoupon::STATUS_NOT_USE;
        $userCoupons = UserCoupon::where($userCouponWhere)->with('coupons')->whereHas(
            'coupons', function ($query) use ($totalPrice) {
            $query->where('min_amount','<=', $totalPrice);
        }
        )->get();

        $currentCheckedCouponInfo = '';
        if ($user_coupon_id) {
            $currentCheckedCouponInfo = $userCoupons->where('id',$user_coupon_id)->first();
            // 把订单金额修改为优惠后的金额
            $totalPrice = $currentCheckedCouponInfo->coupons->getAdjustedPrice($totalPrice);
        }


        $data['address'] = $addresses;
        $data['cart_items'] = $cartItems;
        $data['total_amount'] = $totalAmount;
        $data['total_price'] = $totalPrice;
        $data['cart_items_arr'] = $cart_items_arr;
        $data['user_coupons'] = $userCoupons;
        $data['user_coupon_info'] = $currentCheckedCouponInfo;

        return new SettlementResource(collect($data));
    }
}
