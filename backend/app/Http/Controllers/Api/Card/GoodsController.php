<?php

namespace App\Http\Controllers\Api\Card;

use App\Http\Resources\CommonResource;
use App\Http\Resources\PaymentResource;
use App\Models\Goods;
use App\Models\GoodsOrder;
use App\Models\User;
use App\Models\User\UserAuth;
use App\Models\User\UserBalanceLog;
use App\Services\GoodsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GoodsController extends Controller
{
    protected $server;

    public function __construct(GoodsService $service)
    {
        $this->server = $service;
    }

    public function list(Request $request){
        $cid = $request->get('cid');
        $keywords = $request->get('keywords');
        $is_show = Goods::IS_SHOW_TRUE;

        $condition = compact('cid', 'keywords', 'is_show');
        $goods = $this->server->list($condition);
        return new CommonResource(compact('goods'));
    }

    public function show(Request $request){
        $id = $request->get('id');
        $goods = $this->server->detail($id);
        if($goods->is_show === Goods::IS_SHOW_FALSE){
            abort(403, '商品已下架');
        }
        $goods->increment('views');
        return new CommonResource(compact('goods'));
    }

    // 下单购买
    public function order(Request $request){
        $id = $request->get('id');
        $goods = $this->server->detail($id);
        if($goods->is_show === Goods::IS_SHOW_FALSE){
            abort(403, '商品已下架');
        }
        $order_no = createOrderNo();
        $user = $request->user();
        if(!$user->carte){
            abort(403, '请完善名片信息');
        }
        $goodsOrder = $this->userOrderLogs($user, $goods->id, $goods->price, $order_no);
        if(bccomp($goods->price, 0.00, 2) === 1){
            try{
                DB::beginTransaction();
                $this->userBalanceLogs($user, $goods->price);

                // 统一下单
                $userAuthWhere['user_id'] = $user->id;
                $userAuthWhere['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
                $userOpenId = UserAuth::where($userAuthWhere)->value('identifier');
                $money = bcmul($goods->price,100);
                $order = [
                    'out_trade_no' => $order_no,
                    'body' => '购买商品',
                    'total_fee' => $money,
                    'openid' => $userOpenId,
                    'notify_url' => route('goods.wechat.notify'),
                ];
                $pay = app('wechat_pay')->miniapp($order);
                DB::commit();
                return new PaymentResource($pay);
            }catch (\Exception $exception){
                DB::rollBack();
                \Log::error($exception->getTraceAsString());
                abort(403, $exception->getMessage());
            }
        }else{
            $this->paySuccess($goodsOrder->id);
        }
    }

    public function wechatPayNotify(Request $request){
        DB::beginTransaction();
        try{
            // 校验回调参数是否正确
            $data = app('wechat_pay')->verify();

            $goodsOrder = GoodsOrder::query()->where('order_sm', $data->out_trade_no)->first();
            if(!$goodsOrder){
                return 'fail';
            }
            if($goodsOrder->is_pay === GoodsOrder::IS_PAY_TRUE){
                return app('wechat_pay')->success();
            }



            $this->paySuccess($goodsOrder->id, $data->transaction_id);
            DB::commit();
            return app('wechat_pay')->success();
        }catch (\Exception $exception){
            DB::rollBack();
            \Log::error($exception->getTraceAsString());
            return 'fail';
        }
    }


    // 支付成功后逻辑
    private function paySuccess($id, $pay_sm = null){
        $this->server->paySuccess($id, $pay_sm);
    }

    // 会员流水
    protected function userBalanceLogs(User $user, $money){
        return UserBalanceLog::addLog($user->id, UserBalanceLog::LOG_TYPE_PAY, UserBalanceLog::TYPE_BUY_GOODS, $money);
    }

    // 订单
    protected function userOrderLogs(User $user, $goods_id, $money, $order_sm){
        return GoodsOrder::addLog($user->id, $goods_id, $money, $order_sm);
    }
}
