<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardNote extends Model
{
    protected $table = 'reward_notes';     // 数据表名
    public static $snakeAttributes = false;   // 设置关联模型在打印输出的时候是否自动转为蛇型命名
    protected $guarded = ['id'];        // 过滤的字段

}
