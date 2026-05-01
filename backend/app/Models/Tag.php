<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tag'; // 标签表

    protected $fillable = ['uid', 'type', 'other_uid', 'info_id','status', 'title'];

    const TYPE_OWN = 1; // 个人名片标签
    const TYPE_OTHER_PERSON = 2; // 个人记录别人名片更新

    const STATUS_NORMAL = 1; // 正常
    const STATUS_DELETE = 99; // 删除
}
