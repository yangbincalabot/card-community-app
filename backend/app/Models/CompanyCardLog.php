<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyCardLog extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    // 企业会员开通记录

    const PAY_WECHAT = 1; // 微信支付
    const PAY_BALANCE = 2; // 余额支付

    const PAY_PAID = true; // 已支付
    const PAY_UNPAID = false; // 未支付

    protected $dates = ['deleted_at', 'paid_at'];
    protected $casts = [
        'is_pay' => 'boolean', // 是否支付
    ];


    public static function getPayments($type){
        $pays = [
            self::PAY_WECHAT => '微信支付',
            self::PAY_BALANCE => '余额支付'
        ];
        return $pays[$type] ?? '未知类型';
    }

    public static function getPayStatus($status = null){
        $payStatus = [
            self::PAY_PAID => '已支付',
            self::PAY_UNPAID => '未支付'
        ];
        if($status){
            return $payStatus[$status] ?? '未知状态';
        }
        return $payStatus;
    }

    // 添加记录(支付前)
    public static function addLogs($user_id, $money, $order_no, $pay_type, $remark = ''){
        return self::create([
            'user_id' => $user_id,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'money' => moneyShow($money),
        ]);
    }

    // 所属会员
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

}
