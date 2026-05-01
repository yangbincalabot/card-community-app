<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class VerificationCode extends Model
{
    protected $guarded = ['id'];
    use Notifiable, SoftDeletes;
    protected $dates = ['deleted_at'];


    const CHANNEL_SMS = 'sms';

    const STATUS_UNUSED = 1; // 验证码未验证
    const STATUS_USED = 2; // 验证码已校验



    public function routeNotificationFor($driver, $notification = null){
        return $this->account;
    }

    public function user(){
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    public static function getLatestSmsCode($user_id){
        return self::where('user_id', $user_id)->where('state', self::STATUS_UNUSED)->where('channel', self::CHANNEL_SMS)->latest()->first();
    }

    public static function getTodaySmsNums($user_id){
        return self::query()->where('user_id', $user_id)->where('channel', self::CHANNEL_SMS)->whereBetween('created_at', [
            Carbon::today(), // 今天开始时间（00:00:00）
            Carbon::tomorrow()->subSeconds(1), // 今天结束时间（23:59:59）
        ])->count();
    }

    /**
     * 短信发送频率限制
     * @param $user_id
     * @param $minutes
     * @return bool
     */
    public static function checkSendRate($user_id, $minutes = 1){
        if(!is_numeric($minutes) || $minutes < 0){
            return false;
        }
        $lastSend = self::where('user_id', $user_id)->where('channel', self::CHANNEL_SMS)->latest()->first();
        if($lastSend){
            $lastSendTime = $lastSend->created_at;
            if(!$lastSendTime){
                return true;
            }
            if(Carbon::now()->lt($lastSendTime->addMinutes($minutes))){
                return false;
            }
        }
        return true;
    }
}
