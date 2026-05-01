<?php

namespace App\Models\Activity;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $table = 'agenda'; // 会务议程表

    protected $fillable = ['uid', 'aid', 'presenter', 'pid', 'title', 'start_time', 'end_time'];

    protected $datetimes = ['start_time','end_time'];

    public function getStartTimeAttribute($time)
    {
        return substr($time,0,5);
    }

    public function getEndTimeAttribute($time)
    {
        return substr($time,0,5);
    }
}
