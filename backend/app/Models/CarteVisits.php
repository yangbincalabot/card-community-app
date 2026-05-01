<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CarteVisits extends Model
{
    protected $fillable = ['user_id', 'carte_id', 'last_view_time', 'week_nums'];
    protected $dates = ['last_view_time'];

    protected $appends = [
        'views_this_week', // 本周浏览数
    ];

    // 添加浏览记录
    public static function addCarteVisits($carte_id, $user_id){
        return self::create([
            'carte_id' => $carte_id,
            'user_id' => $user_id,
            'last_view_time' => Carbon::now(),
            'week_nums' => 0,
        ]);
    }

    // 浏览人的信息
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }


    // 是否本周结束
    public static function isWeekEnd(Carbon $last_view_time){
        // 上次访问时间的星期时间段， 0为星期天
        $last_view_week = $last_view_time->dayOfWeek;
        $last_view_time_start = Carbon::create($last_view_time->year, $last_view_time->month, $last_view_time->day, 0)->toDateTimeString();
        $last_view_time_end = Carbon::create($last_view_time->year, $last_view_time->month, $last_view_time->day, 23, 59, 59)->toDateTimeString();
        if($last_view_week > 0){
            $last_view_week_start = Carbon::parse($last_view_time_start)->subDays($last_view_week - 1);
            $last_view_week_end = Carbon::parse($last_view_time_end)->addDays(7 - $last_view_week);
        }elseif ($last_view_week === 1){
            $last_view_week_start = Carbon::parse($last_view_time_start);
            $last_view_week_end = Carbon::parse($last_view_time_end)->addDays(7 - $last_view_week);
        }else{
            $last_view_week_start = Carbon::parse($last_view_time_start)->subDays(6);
            $last_view_week_end = Carbon::parse($last_view_time_end);
        }
        $now = Carbon::now();
        return !($now->gte($last_view_week_start) && $now->lte($last_view_week_end));
    }

    public function getViewsThisWeekAttribute(){
        if(isset($this->attributes['last_view_time'])){
            // 不是本周
            if(self::isWeekEnd($this->last_view_time)){
                return 0;
            }else{
                return $this->attributes['week_nums'];
            }
        }else{
            return 0;
        }
    }
}
