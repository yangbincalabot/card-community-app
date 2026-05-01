<?php

namespace App\Models\User;

use App\Models\Activity\Activity;
use App\Models\Carte;
use App\Models\Product;
use App\Models\Supply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Collection extends Model
{
    protected $table = 'user_collection';//设置表名

    protected $fillable = [
        'uid', 'type','info_id','status'
    ];

    // 提交状态：1.已收藏；2.已取消
    const COLLECTION_STATUS_ONE = 1;
    const COLLECTION_STATUS_TWO = 2;


    // 收藏类型：1.名片；2.供需; 3.活动
//    const COLLECTION_TYPE_ONE = 1;
    const COLLECTION_TYPE_TWO = 2;
    const COLLECTION_TYPE_THREE = 3;


    public function getCollectionStatus($type = '') {
        $data = [
            self::COLLECTION_STATUS_ONE => '已收藏',
            self::COLLECTION_STATUS_TWO => '已取消'
        ];
        return $data[$type] ?? $data;
    }

    public function getCollectionType($type = '') {
        $data = [
//            self::COLLECTION_TYPE_ONE => '名片',
            self::COLLECTION_TYPE_TWO => '供需',
            self::COLLECTION_TYPE_THREE => '活动'
        ];
        return $data[$type] ?? $data;
    }

    public function supply(){
        return $this->belongsTo(Supply::class,'info_id');
    }

    public function activity(){
        return $this->belongsTo(Activity::class,'info_id');
    }


}
