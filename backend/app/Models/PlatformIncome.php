<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformIncome extends Model
{
    use SoftDeletes;
    // 平台收益

    // 收益类型
    const COMPANY_TYPE = 1; // 开通企业会员
    const ACTIVE_TYPE = 2; // 活动分佣

    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getTypes($type = null){
        $types = [
            self::COMPANY_TYPE => '开通企业会员',
            self::ACTIVE_TYPE => '活动分佣',
        ];
        if(empty($type)){
            return $types;
        }
        return $types[$type] ?? '未知类型';
    }


    // 添加平台收益
    public static function addPlatformIncome($user_id, $type, $money, $info_id = 0, $remark = ''){
        return self::create([
           'user_id' => $user_id,
           'type' => $type,
           'money' => $money,
           'info_id' => $info_id,
           'remark' => $remark
        ]);
    }


}
