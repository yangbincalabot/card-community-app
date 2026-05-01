<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserBalanceLog extends Model
{
    protected $fillable = ['user_id', 'log_type', 'type', 'money', 'remark', 'from_user_id', 'order_id', 'activity_id', 'apply_id'];

    // 日志操作类型(log_type)
    const LOG_TYPE_INCOME = 1; // 收入
    const LOG_TYPE_PAY = 2; // 支出



    // 具体操作类型(type)
    const TYPE_WITHDRAW = 1; // 提现
    const TYPE_ACTIVITY_FEE = 2; // 参加活动报名费用
    const TYPE_COMPANY_FEE = 3; // 开通企业会员费用
    const TYPE_BACKEND_RECHARGE = 4; // 后台充值
    const TYPE_ACTIVITY_REWARD = 5; // 发起活动收益，此收益是扣掉平台佣金后的收益
    const TYPE_ACTIVITY_REFUND = 6; // 活动退款
    const TYPE_BUY_GOODS = 7; // 购买商品支出
    const TYPE_SALES_GOODS = 8; // 出售商品收入
    const TYPE_APPLICATION_SOCIETY_PAY = 9; // 申请协会费用支出
    const TYPE_APPLICATION_SOCIETY_INCOME = 10; // 协会创建者收入
    const TYPE_APPLICATION_REFUND = 11; // 拒绝通过时退款


    // 具体操作类型提示文字
    public static function getTypeText($type = null){
        $type_texts = [
            self::TYPE_WITHDRAW => '提现',
            self::TYPE_ACTIVITY_FEE => '活动支出',
            self::TYPE_COMPANY_FEE => '开通企业会员',
            self::TYPE_BACKEND_RECHARGE => "后台充值",
            self::TYPE_ACTIVITY_REWARD => '发起活动收益',
            self::TYPE_ACTIVITY_REFUND => '活动退款',
            self::TYPE_BUY_GOODS => '购买商品',
            self::TYPE_SALES_GOODS => '出售商品收入',
            self::TYPE_APPLICATION_SOCIETY_PAY => '申请协会支出',
            self::TYPE_APPLICATION_SOCIETY_INCOME => '申请协会收入',
            self::TYPE_APPLICATION_REFUND => '申请协会费用退款',
        ];
        if(empty($type)){
            return $type_texts;
        }
        return $type_texts[$type] ?? $type;
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fromUser(){
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public static function getLogType($log_type = null){
        $log_types = [
            self::LOG_TYPE_INCOME => '收入',
            self::LOG_TYPE_PAY => '支出',
        ];
        if(empty($log_type)){
            return $log_types;
        }
        return $log_types[$log_type] ?? $log_type;
    }

    /**
     * 添加流水记录
     * @param $user_id
     * @param $log_type
     * @param $type
     * @param $money
     * @param string $remark '为空时使用默认type文字提示'
     * @param int $activity_id
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public static function addLog($user_id, $log_type, $type, $money, $remark = '', $activity_id = 0, $apply_id = 0){
        $money = in_array($log_type, [self::LOG_TYPE_PAY]) ? -$money : $money;
        $remark = $remark ? $remark :self::getTypeText($type);
        return self::query()->create([
            'user_id' => $user_id,
            'log_type' => $log_type,
            'type' => $type,
            'money' => $money,
            'remark' => $remark,
            'activity_id' => $activity_id,
            'apply_id' => $apply_id
        ]);
    }

}
