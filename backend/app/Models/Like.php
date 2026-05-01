<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $table = 'like'; // 点赞表

    protected $fillable = ['uid', 'info_id', 'type', 'status'];

    const TYPE_SUPPLY = 1; // 供需

    // 提交状态：1.已赞；2.已取消
    const STATUS_ONE = 1;
    const STATUS_TWO = 2;

    public function getLikeStatus($type = '') {
        $data = [
            self::STATUS_ONE => '已赞',
            self::STATUS_TWO => '已取消'
        ];
        return $data[$type] ?? $data;
    }

}
