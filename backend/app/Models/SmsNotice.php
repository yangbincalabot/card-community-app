<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SmsNotice extends Model
{
    protected $table = 'sms_notice';

    protected $guarded = ['id'];

    const STATUS_SUCCESS = 1; // 发送成功

    const MAX_LENGTH = 3; //用户每天最大发送短信条数

    const ERROR_MSG = '发送失败，数据为空';

    public static function addNotice($uid, $name, $phone) {
        self::query()->create([
            'uid' => $uid,
            'name' => $name,
            'phone' => $phone,
            'status' => self::STATUS_SUCCESS,
        ]);
    }


    public static function checkSendFrequency($uid) {
        if (empty($uid)) {
            return ['status' => 0, 'msg' => '用户不存在，请退出重新登录'];
        }
        $count = self::query()->where('uid', $uid)->where('created_at', '>', Carbon::today()->toDateTimeString())->count();

        if ($count >= self::MAX_LENGTH) {
            return ['status' => 0, 'msg' => '您今天发送短信条数已达到最大值'];
        }
        return ['status' => 1];
    }

}
