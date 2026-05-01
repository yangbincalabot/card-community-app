<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class ScanLog extends Model
{
    protected $fillable = ['user_id', 'api_type'];


    public static function addScanLog($user_id, $api_type){
        return self::create([
            'user_id' => $user_id,
            'api_type' => $api_type
        ]);
    }

    public static function getTodayScanNums($user_id){
        return self::query()->where('user_id', $user_id)->whereBetween('created_at', [
            Carbon::today(), // 今天开始时间（00:00:00）
            Carbon::tomorrow()->subSeconds(1), // 今天结束时间（23:59:59）
        ])->count();
    }
}
