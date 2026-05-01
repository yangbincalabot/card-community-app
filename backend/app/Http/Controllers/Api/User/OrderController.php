<?php

namespace App\Http\Controllers\Api\User;


use App\Http\Resources\CommonResource;
use App\Models\CompanyCard;
use App\Models\GoodsOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    // 获取用户订单，暂时只获取付款成功的订单
    public function index(Request $request){
        $user = $request->user();
        $orders = GoodsOrder::query()->where([
            'user_id' => $user->id,
            'is_pay' => GoodsOrder::IS_PAY_TRUE
        ])->with(['goods' => function($query){
            $query->with('company');
        }, 'user'])->latest()->paginate();

        // 商家电话
        foreach ($orders as $order){
            $company = $order->goods->company;
            $phone = $company->contact_number;
            if(!$phone){
                $company->user->phone;
            }
            $order->phone = $phone;
        }
        return new CommonResource(compact('orders'));
    }
}
