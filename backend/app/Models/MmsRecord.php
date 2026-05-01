<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MmsRecord extends Model
{
    protected $table = 'mms_record';

    protected $guarded = ['id'];

    const STATUS_ZERO = 0; // 发送中
    const STATUS_ONE = 1; // 发送成功
    const STATUS_TWO = 2; // 发送失败

    const MAX_LENGTH = 3; //用户每天最大发送彩信条数

    public static function addRecord($uid, $phone, $send_id, $content = '') {
//        $content = $content?:'请打开手机微信扫码进入小程序，完善名片信息';
        $content = $content?:'请尽快完善名片内容';
        self::query()->create([
            'uid' => $uid,
            'phone' => $phone,
            'send_id' => $send_id,
            'status' => self::STATUS_ZERO,
            'content' => $content,
        ]);
    }


    public static function checkSendFrequency($uid) {
        if (empty($uid)) {
            abort(403, '用户不存在，请退出重新登录');
        }
        $count = self::query()->where('uid', $uid)->where('created_at', '>', Carbon::today()->toDateTimeString())->count();
        if ($count > self::MAX_LENGTH) {
            abort(403, '您今天发送彩信条数已达到最大值');
        }
    }

}
