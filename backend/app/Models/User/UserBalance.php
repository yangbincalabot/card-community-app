<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserBalance extends Model
{
    protected $fillable = ['user_id', 'money', 'frozen_money', 'total_revenue', 'key'];
    protected $hidden = ['key'];
    const USER_DEFAULT_MONEY = 0; // 默认可用金额

    // 添加默认数据
    // TODO 此方法还能继续完善，等最后流水表完善后再处理
    public static function addDefaultData($user_id){
        $userBalance = self::create([
           'user_id' => $user_id,
            'money' => self::USER_DEFAULT_MONEY,
            'key' => self::encryptKey(self::USER_DEFAULT_MONEY, self::USER_DEFAULT_MONEY, self::USER_DEFAULT_MONEY)
        ]);
        return $userBalance;
    }

    // 用户资金的密钥key
    public static function encryptKey($money, $frozen_money, $total_revenue){
        foreach(compact('money', 'frozen_money', 'total_revenue') as $key => $value) {
            if((!is_numeric($value)) || $value < 0){
                ${$key} = self::USER_DEFAULT_MONEY;
            }
            ${$key} = moneyShow($value);
        }
        return sha1(sha1($money . $frozen_money . $total_revenue));
    }

    // 验证资金的密钥
    public static function checkKey($key, $user_id){
        $userBalance = self::query()->where('user_id', $user_id)->first();
        if(!$userBalance){
            self::addDefaultData($user_id);
            return false;
        }
        $targetKey = sha1(sha1($userBalance->money . $userBalance->frozen_money . $userBalance->total_revenue));
        return strcmp($key, $targetKey) === 0;
    }
}
