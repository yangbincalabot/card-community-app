<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CouponCodeUnavailableException;
use App\Http\Requests\ApplyRefundRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Configure;
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\User;
use App\Models\User\UserCoupon;
use App\Models\UserAddress;
use App\Services\OrderService;
use App\Services\SalesCommissionsService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function index(Order $order, Request $request){
        $statusType = $request->get('status_type','all');
        $shipStatusWhere = $this->getConditionByStatusType($statusType);
        if(!isset($shipStatusWhere['closed'])){
            if(!empty($shipStatusWhere)){
                $shipStatusWhere['closed'] = false;
            }
        }

        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->where('refund_status', Order::REFUND_STATUS_PENDING)
            ->where(function($query) use($shipStatusWhere) {
                if(!empty($shipStatusWhere)){
                    $query->where($shipStatusWhere);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate();
        foreach ($orders as $orderItem){
            $orderItem->status_cn = $order->getStatusTitle($orderItem->ship_status);
            $orderItem->total_sku_num = $orderItem->items->sum('amount');
        }
        return new OrderResource($orders);
    }

    public function refundOrders(Order $order, Request $request){
        $statusType = $request->get('status_type','refund_all');
        $refundStatusWhere = $this->getConditionByStatusType($statusType);
        $refundStatusGroup = [$order::REFUND_STATUS_APPLIED,$order::REFUND_STATUS_PROCESSING,$order::REFUND_STATUS_SUCCESS,$order::REFUND_STATUS_FAILED];
        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->where(function($query) use($refundStatusWhere,$refundStatusGroup) {
                if(!empty($refundStatusWhere)){
                    $query->where($refundStatusWhere);
                }else{
                    $query->whereIn('refund_status',$refundStatusGroup);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate();
        foreach ($orders as $orderItem){
            $orderItem->status_cn = $order->getStatusTitle($orderItem->ship_status);
            $orderItem->refund_status_cn = $order->getRefundStatusTitle($orderItem->refund_status);
            $orderItem->total_sku_num = $orderItem->items->sum('amount');
        }
        return new OrderResource($orders);
    }

    public function getConditionByStatusType($statusType){
        $orderModel = new Order();
        $where = [];
        switch ($statusType){
            case 'pending':
                $where['ship_status'] = $orderModel::SHIP_STATUS_PENDING;
                break;
            case 'offline_pending':
                $where['ship_status'] = $orderModel::SHIP_STATUS_OFFLINE_PENDING;
                break;
            case 'delivering':
                $where['ship_status'] = $orderModel::SHIP_STATUS_DELIVERING;
                break;
            case 'delivered':
                $where['ship_status'] = $orderModel::SHIP_STATUS_DELIVERED;
                break;
            case 'received':
                $where['ship_status'] = $orderModel::SHIP_STATUS_RECEIVED;
                break;
            case 'cancel':
                $where['closed'] = true;
                break;
            case 'refund_all':
                $where = [];
                break;
            case 'refund_applied':
                $where['refund_status'] = $orderModel::REFUND_STATUS_APPLIED;
                break;
            case 'refund_processing':
                $where['refund_status'] = $orderModel::REFUND_STATUS_PROCESSING;
                break;
            case 'refund_failed':
                $where['refund_status'] = $orderModel::REFUND_STATUS_FAILED;
                break;
            case 'refund_success':
                $where['refund_status'] = $orderModel::REFUND_STATUS_SUCCESS;
                break;
            default:
                $where = [];
                break;
        }
        return $where;
    }

    public function show(Order $order, Request $request)
    {
        $user_id = $request->user()->id;
        $orderId = $request->get('id');
        $where['id'] = $orderId;
        $where['user_id'] = $user_id;
        $orderInfo = $order->where($where)->with(['items.productSku', 'items.product'])->first();

        $orderInfo->status_cn = $order->getStatusTitle($orderInfo->ship_status);
        $orderInfo->total_sku_num = $orderInfo->items->sum('amount');

        // 获取当前订单中的所有商品
        $productData = $orderInfo->items;
        foreach ($orderInfo->items as $item){
            $item->hj_amount = bcmul($item->amount,$item->product->price,2);
            $item->exclusive_amount = bcmul($item->amount,$item->product->exclusive_price,2);
        }
        // 获取合计金额（按原价计算订单价格）
        $hj_amount = $orderInfo->items->sum('hj_amount');
        $exclusive_amount = $orderInfo->items->sum('exclusive_amount');

        // 检查是否使用了优惠券，如果使用了优惠券，获取该优惠券的金额
        $coupon_money = 0;
        // 计算代理拿货折扣 合计金额减专属价购买的金额
        $exclusive_price_diff = bcsub($hj_amount,$exclusive_amount,2);
        $orderInfo->hj_amount = $hj_amount;
        $orderInfo->coupon_money = $coupon_money;
        $orderInfo->exclusive_price_diff = $exclusive_price_diff;
        return new OrderResource($orderInfo);
    }

    public function changeStatus(Order $order, Request $request){

    }


    public function applyRefund(Order $orderModel, ApplyRefundRequest $request)
    {
        $user_id = $request->user()->id;
        $orderId = $request->get('id');
        $where['id'] = $orderId;
        $where['user_id'] = $user_id;
        $order = $orderModel->where($where)->with(['items.productSku', 'items.product'])->first();
        if(!$order){
            abort(403,'订单不存在');
        }
        // 判断订单是否已付款
        if ((!$order->paid_at) && (!$order->confirm_offline_pay_at)) {
            abort(403,'该订单未支付，不可退款');
        }
        // 众筹订单不允许申请退款
        if ($order->type === Order::TYPE_CROWDFUNDING) {
            abort(403,'众筹订单不支持退款');
        }
        // 判断订单退款状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            abort(403,'该订单已经申请过退款，请勿重复申请');
        }
        // 将用户输入的退款理由放到订单的 extra 字段中
        $extra                  = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');
        // 将订单退款状态改为已申请退款
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);

        return $order;
    }

    public function received(Request $request,Order $order,SalesCommissionsService $salesCommissionsService)
    {
        $user_id = $request->user()->id;
        $orderId = $request->get('id');
        $where['id'] = $orderId;
        $where['user_id'] = $user_id;
        $orderInfo = $order->where($where)->first();
        // 判断订单的发货状态是否为已发货
        if ($orderInfo->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            abort(403,'发货状态不正确');
        }

        DB::transaction(function () use ($orderInfo,$user_id,$salesCommissionsService) {
            // 更新发货状态为已收到
            $orderInfo->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);
            $user = User::where('id',$user_id)->first();
            $salesCommissionsService->enterAccount($user,$orderInfo->total_amount,$orderInfo->id);
        });

        return $orderInfo;
    }

    public function store(OrderRequest $request, OrderService $orderService){
        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));
        $userCouponInfo  = null;

        // 如果用户提交了优惠券
        if ($user_coupon_id = $request->input('user_coupon_id')) {

            // 获取用户已有且当前订单可以使用的优惠券
            $userCouponWhere['user_id'] = $user->id;
            $userCouponWhere['status'] = UserCoupon::STATUS_NOT_USE;
            $userCouponWhere['id'] = $user_coupon_id;
            $userCouponInfo = UserCoupon::where($userCouponWhere)->with('coupons')->first();
            if(empty($userCouponInfo)){
                abort(403,'优惠券不存在，或已使用');
            }
        }
        // 参数中加入 $coupon 变量
        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'), $userCouponInfo);
    }


    // 选择支付方式
    public function orderDetailBySelectedPayType(Order $order, Request $request)
    {
        $id = $request->get('id');
        $user_id = $request->user()->id;
        // 检查订单id是否属于当前用户
        $where['id'] = $id;
        $where['user_id'] = $user_id;
        $where['ship_status'] = Order::SHIP_STATUS_PENDING;
        $orderInfo = $order->where($where)->first();
        // 获取订单信息
        return new OrderResource($orderInfo);
    }

    // 获取在线支付时的订单信息以及平台收款账户信息
    public function orderDetailByOffline(Order $order, Request $request){
        $id = $request->get('id');
        $user_id = $request->user()->id;
        // 检查订单id是否属于当前用户
        $where['id'] = $id;
        $where['user_id'] = $user_id;
        $where['ship_status'] = Order::SHIP_STATUS_PENDING;
        $orderInfo = $order->where($where)->first();
        // 获取收款银行信息
        $bankData = Configure::where('name','like','BANK_%')->get();
        $bankInfo = [];
        foreach ($bankData as $bankItem){

            $bankInfo[$bankItem->name] = $bankItem->value;
        }
        $data['order_info'] = $orderInfo;
        $data['bank_info'] = $bankInfo;
        return new OrderResource(collect($data));
    }

    // 线下支付提醒
    public function confirmPaid(Order $order, Request $request){
        $id = $request->get('id');
        $user_id = $request->user()->id;
        // 检查订单id是否属于当前用户
        $where['id'] = $id;
        $where['user_id'] = $user_id;
        $where['ship_status'] = Order::SHIP_STATUS_PENDING;
        $orderInfo = $order->where($where)->first();
        if(!$orderInfo){
            abort(403,'当前状态权限不足');
        }
        // 将订单状态改为待确认,支付方式 payment_method 改为线下支付
        $orderInfo->update([
            'ship_status' => Order::SHIP_STATUS_OFFLINE_PENDING,
            // 我们在 Order 模型的 $casts 属性里指明了 ship_data 是一个数组
            'payment_method' => Order::PAYMENT_METHOD_OFFLINE_PAY,
            'remind_at' => Carbon::now()->toDateTimeString()
        ]);
        return new OrderResource($orderInfo);
    }

    // 获取支付成功后的订单详情，如果是线下支付，则显示需要等待后台确认
    public function orderDetailBySuccessPay(Order $order, Request $request)
    {
        $id = $request->get('id');
        $user_id = $request->user()->id;
        // 检查订单id是否属于当前用户
        $where['id'] = $id;
        $where['user_id'] = $user_id;
        $orderInfo = $order->where($where)->first();
        if(!$orderInfo){
            abort(404,'信息不存在');
        }
        // 获取订单信息
        return new OrderResource($orderInfo);
    }

    public function expressType(Order $order,Request $request){
        $id = $request->get('id');
        $user_id = $request->user()->id;
        $where['id'] = $id;
        $where['user_id'] = $user_id;
        $orderInfo = $order->where($where)->first();
        if(!$orderInfo){
            abort(404,'信息不存在');
        }
        if(!$orderInfo->ship_data){
            abort(404,'当前订单未填写发货信息，无法查询物流状态');
        }
        $ship_data = $orderInfo->ship_data;
        $expressNo = $ship_data['express_no'];
        // 获取订单类型
//        $orderShipInfo = $this->getExpressType($expressNo);
        $minutes = 5;
        $orderShipInfo = Cache::remember($id.'_'.$expressNo, $minutes, function () use ($expressNo) {
            return $this->getExpressType($expressNo);
        });


        $orderShipTypeCode = $orderShipInfo['auto'][0]['comCode'];
        return new OrderResource(collect(['com_code'=>$orderShipTypeCode]));
    }

    public function express(Order $order,Request $request){
        $id = $request->get('id');
        $comCode = $request->get('com_code');
        $phone = $request->get('phone','');
        $user_id = $request->user()->id;
        $where['id'] = $id;
        $where['user_id'] = $user_id;
        $orderInfo = $order->where($where)->first();
        if(!$orderInfo){
            abort(404,'信息不存在');
        }
        if(!$orderInfo->ship_data){
            abort(404,'当前订单未填写发货信息，无法查询物流状态');
        }
        $ship_data = $orderInfo->ship_data;
        $expressNo = $ship_data['express_no'];
        // 获取物流信息
//        $orderShipInfo = $this->getExpress($comCode,$expressNo,$phone);
        $minutes = 5;
        $orderShipInfo = Cache::remember($comCode.'_'.$expressNo, $minutes, function () use ($comCode,$expressNo,$phone) {
            return $this->getExpress($comCode,$expressNo,$phone);
        });
        return new OrderResource(collect($orderShipInfo));
    }

    public function getExpress($comCode,$postid,$phone = ''){
        $get_uri = 'https://www.kuaidi100.com/query';
        $client = new Client([// Base URI is used with relative requests
            'base_uri' => $get_uri ,
            // You can set any number of default request options.
            'timeout'  => 10.0 ,
            'headers'  => [
                'Accept'          => 'application/json, text/javascript, */*; q=0.01' ,
                'Accept-Encoding' => 'gzip, deflate, br' ,
                'Accept-Language' => 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7' ,
                'Connection'         => 'keep-alive' ,
                'Cookie'         => 'csrftoken=67mkYAEAWU8kLNx1l5ERlwX9iuDTbV4UDiBxAvHNFxs; sortStatus=0; Hm_lvt_22ea01af58ba2be0fec7c11b25e88e6c=1561305999,1561341706; WWWID=WWW8ACDBA3B1A4F8F8E8A1EF1BA140212B7; Hm_lpvt_22ea01af58ba2be0fec7c11b25e88e6c=1561343110',
                'Host'         => 'www.kuaidi100.com' ,
                'Referer'         => 'https://www.kuaidi100.com/' ,
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
                'X-Requested-With'      => 'XMLHttpRequest'
            ]
        ]);

        if($comCode != 'shunfeng'){
            $phone = '';
        }

        Log::info('getExpress',[
            'type' => $comCode,
            'postid' => $postid,
            'temp' => $this->random(),
            'phone' => $phone,
        ]);



        $response = $client->get($get_uri,[
            'query' => [
                'type' => $comCode,
                'postid' => $postid,
                'temp' => $this->random(),
                'phone' => $phone,
            ]
        ]);

        if($response->getStatusCode() == 200)
        {
            $json_str = $response->getBody();
            $json_str = mb_convert_encoding($json_str,"utf8","UTF-8");
//            $html = str_replace('charset=gb2312','charset=UTF-8',$html);
            $json_arr = json_decode($json_str,true);
            return $json_arr;
        }
        return false;

    }


    public function getExpressType($expressNo){

        $get_uri = 'https://www.kuaidi100.com/autonumber/autoComNum';
        $client = new Client([// Base URI is used with relative requests
            'base_uri' => $get_uri ,
            // You can set any number of default request options.
            'timeout'  => 10.0 ,
            'headers'  => [
                'Accept'          => 'application/json, text/javascript, */*; q=0.01' ,
                'Accept-Encoding' => 'gzip, deflate, br' ,
                'Accept-Language' => 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7' ,
                'Connection'         => 'keep-alive' ,
                'Cookie'         => 'sortStatus=0; Hm_lvt_22ea01af58ba2be0fec7c11b25e88e6c=1561305999,1561341706; WWWID=WWW8ACDBA3B1A4F8F8E8A1EF1BA140212B7; Hm_lpvt_22ea01af58ba2be0fec7c11b25e88e6c=1561345379',
                'Host'         => 'www.kuaidi100.com' ,
                'Referer'         => 'https://www.kuaidi100.com/' ,
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
                'X-Requested-With'      => 'XMLHttpRequest'
            ]
        ]);

        Log::info('getExpressType',[
            'resultv2' => 1,
            'text' => $expressNo,
        ]);


        $response = $client->post($get_uri,[
            'query' => [
                'resultv2' => 1,
                'text' => $expressNo,
            ]
        ]);

        if($response->getStatusCode() == 200)
        {
            $json_str = $response->getBody();
            $json_str = mb_convert_encoding($json_str,"utf8","UTF-8");
//            $html = str_replace('charset=gb2312','charset=UTF-8',$html);
            $json_arr = json_decode($json_str,true);
            return $json_arr;
        }
        return false;

    }

    function random($min = 0, $max = 1)
    {
        return $min + mt_rand()/mt_getrandmax()*($max-$min);
    }
}
