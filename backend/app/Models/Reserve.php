<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    protected $table = 'reserve'; // 预约名片

    protected $guarded = ['id'];

    const RESERVE_BOOKED = 1; // 已预约
    const RESERVE_CANCELLED = 2; // 已取消
}
