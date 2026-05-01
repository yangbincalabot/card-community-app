<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Resources\CommonResource;
use App\Models\CompanyCard;
use App\Models\Goods;
use App\Models\GoodsOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    // 获取订单
    public function index(Request $request){
        $user = $request->user();
        $cid = CompanyCard::query()->where('uid', $user->id)->value('id');
        $goodsIds = Goods::query()->where('cid', $cid)->pluck('id')->toArray();
        $orders = collect();

        if($goodsIds){
            $orders = GoodsOrder::query()->whereIn('goods_id', $goodsIds)->with(['goods' => function($query){
                $query->with('company');
            }, 'user'])->latest()->paginate();

            // 买家电话
            foreach ($orders as $order){
                $carte = $order->user->carte;
                $phone = $carte->phone;
                if(!$phone){
                    $phone->$order->user->phone;
                }
                $order->phone = $phone;
            }
        }

        return new CommonResource(compact('orders'));
    }
}
