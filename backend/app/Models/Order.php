<?php

namespace App\Models;

use App\Models\User\UserCoupon;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Order extends Model
{
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_APPLIED = 'applied';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';


    const SHIP_STATUS_PENDING = 'pending';  // 待付款
    const SHIP_STATUS_OFFLINE_PENDING = 'offline_pending';    // 线下付款待确认
    const SHIP_STATUS_DELIVERING = 'delivering';    // 待发货
    const SHIP_STATUS_DELIVERED = 'delivered';      // 已发货
    const SHIP_STATUS_RECEIVED = 'received';        // 已收货
    const SHIP_STATUS_CANCEL = 'cancel';        // 已取消


    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL = 'seckill';

    const PAYMENT_METHOD_OFFLINE_PAY = 'offline_pay';
    const PAYMENT_METHOD_ALI_PAY = 'alipay';
    const PAYMENT_METHOD_WX_PAY = 'wechat';

    public static $typeMap = [
        self::TYPE_NORMAL => '普通商品订单',
        self::TYPE_CROWDFUNDING => '众筹商品订单',
        self::TYPE_SECKILL => '秒杀商品订单',
    ];

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING    => '未退款',
        self::REFUND_STATUS_APPLIED    => '已申请退款',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_SUCCESS    => '退款成功',
        self::REFUND_STATUS_FAILED     => '退款失败',
    ];

    public static $shipStatusMap = [
        self::SHIP_STATUS_PENDING   => '待付款',
        self::SHIP_STATUS_OFFLINE_PENDING   => '线下支付待确认',
        self::SHIP_STATUS_DELIVERING   => '待发货',
        self::SHIP_STATUS_DELIVERED => '已发货',
        self::SHIP_STATUS_RECEIVED  => '已完成',
    ];

    protected $fillable = [
        'type',
        'no',
        'address',
        'total_amount',
        'remark',
        'paid_at',
        'user_coupon_id',
        'discount_money',
        'payment_method',
        'payment_no',
        'refund_status',
        'refund_no',
        'closed',
        'reviewed',
        'ship_status',
        'ship_data',
        'extra',
        'remind_at',
        'confirm_offline_pay_at'
    ];

    protected $casts = [
        'closed'    => 'boolean',
        'reviewed'  => 'boolean',
        'address'   => 'json',
        'ship_data' => 'json',
        'extra'     => 'json',
    ];

    protected $dates = [
        'paid_at',
        'remind_at',
        'confirm_offline_pay_at'
    ];

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            // 如果模型的 no 字段为空
            if (!$model->no) {
                // 调用 findAvailableNo 生成订单流水号
                $model->no = static::findAvailableNo();
                // 如果生成失败，则终止创建订单
                if (!$model->no) {
                    return false;
                }
            }
        });
    }

    public function getStatusTitle($status = ''){
        $data = self::$shipStatusMap;
        return $data[$status] ?? $data;
    }

    public function getRefundStatusTitle($status = ''){
        $data = self::$refundStatusMap;
        return $data[$status] ?? $data;
    }

    public function getStatusByOrderInfo($order){
        // 待付款
        // 线下付款，待确认
        // 线下付款已确认，待发货
        // 线上付款成功，待发货
        // 已发货，待收货
        // 确认收货



        // 待付款
        if(!$order->payment_method){

        }elseif($order->ship_status == Order::SHIP_STATUS_OFFLINE_PENDING){
            // 线下付款，待确认
        }

        $status = '';
        switch ($order->ship_status){
            case self::SHIP_STATUS_OFFLINE_PENDING :
                $status = '线下付款，待确认';
                break;
            case self::SHIP_STATUS_PENDING :
                // 如果退款状态为 pending
                if($order->refund_status === self::REFUND_STATUS_PENDING){
                    // 如果没有支付类型，一定是未付款
                    if(!$order->payment_method){
                        $status = '未付款';
                    } else{
                        // 如果支付类型是线下支付
                        if($order->payment_method == self::PAYMENT_METHOD_OFFLINE_PAY){
                            // 如果有确认线下支付时间
                            if($order->confirm_offline_pay_at){
                                $status = '线下付款已确认，待发货';
                            }
                        }else{
                            if($order->paid_at){
                                $status = '待发货';
                            }elseif($order->closed){
                                $status = '已关闭';
                            }
                        }

                    }
                }else{
                    $status = self::$refundStatusMap[$order->refund_status];
                }
                break;
        }



        if($order->paid_at){
            if($order->refund_status === Order::REFUND_STATUS_PENDING){
                echo '已支付';
            }else{
                echo self::$refundStatusMap[$order->refund_status];
            }
        }elseif ($order->closed){
            echo '已关闭';
        }else{
            echo '未支付';
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function couponCode()
    {
        return $this->belongsTo(CouponCode::class);
    }

    public function user_coupon()
    {
        return $this->belongsTo(UserCoupon::class,'user_coupon_id');
    }

    public static function findAvailableNo()
    {
        // 订单流水号前缀
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6 位的数字
            $no = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }
        \Log::warning('find order no failed');

        return false;
    }

    public static function getAvailableRefundNo()
    {
        do {
            // Uuid类可以用来生成大概率不重复的字符串
            $no = Uuid::uuid4()->getHex();
            // 为了避免重复我们在生成之后在数据库中查询看看是否已经存在相同的退款订单号
        } while (self::query()->where('refund_no', $no)->exists());

        return $no;
    }
}
