<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Resources\CommonResource;
use App\Models\Goods;
use App\Models\GoodsOrder;
use App\Models\User\UserBalanceLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request){
        $user = $request->user();
        $carte = $user->carte;
        if(!$carte || !$carte->cid){
            abort(403, '非法操作');
        }
        $cid = $carte->cid;
        $allGoodsIds = Goods::query()->where('cid', $cid)->pluck('id')->toArray();


        $orders = GoodsOrder::query()->select(DB::raw('goods_orders.*, sum(goods_orders.price) as total_price, max(created_at) as newest_time'))
            ->whereIn('goods_id', $allGoodsIds)
            ->where('is_pay', GoodsOrder::IS_PAY_TRUE)
            ->groupBy('user_id')
            ->with('user')
            ->latest()->paginate();


        return new CommonResource(compact('orders'));
    }
}
