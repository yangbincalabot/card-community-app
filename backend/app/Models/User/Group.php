<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'user_group'; //设置表名

    protected $fillable = [
        'uid', 'title', 'num', 'status'
    ];

    const STATUS_NORMAL = 1; // 正常
    const STATUS_DELETED = 99; // 删除
}
