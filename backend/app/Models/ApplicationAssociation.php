<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationAssociation extends Model
{
    protected $guarded = ['id'];

    // 状态
    const STATUS_NOT_REVIEWED = 0;  // 未审核
    const STATUS_FAILURE = 1; // 审核失败
    const STATUS_SUCCESS = 2; // 审核成功

    const STATUS = [
        self::STATUS_NOT_REVIEWED,
        self::STATUS_FAILURE,
        self::STATUS_SUCCESS,
    ];

    // 类型
    const TYPE_PERSONAL = 1; // 个人认证
    const TYPE_COMPANY = 2; // 企业认证


    // 支付类型
    CONST PAY_TYPE_BALANCE = 1; //余额支付
    const PAY_TYPE_WECHAT = 2; // 微信支付

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function association(){
        return $this->belongsTo(Association::class, 'aid');
    }
}
