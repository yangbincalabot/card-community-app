<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceiveCarte extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    // 收到名片类型
    const TYPE_SCAN = 1; // 扫描
    const TYPE_IMPRESS = 2; // 对方传递
    const TYPE_SHARE = 3; // 分享

    const NOT_REVIEWED = -1; // 未操作
    const NOT_BY_ADDING = 0; // 添加不通过
    const BY_ADDING = 1; // 添加通过

    const HAVE_READ = 1; // 已读
    const UNREAD = 2; // 未读

    // 来源用户信息
    public function fromUser(){
        return $this->belongsTo(User::class, 'from_user_id');
    }

    // 分享者用户信息
    public function shareUser(){
        return $this->belongsTo(User::class, 'share_user_id');
    }

}
