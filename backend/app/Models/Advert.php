<?php

namespace App\Models;

use App\Models\Traits\ImageUrlTrait;
use Illuminate\Database\Eloquent\Model;

class Advert extends Model
{
    use ImageUrlTrait;

    protected $fillable = ['adv_positions_id', 'title', 'image', 'url', 'sort', 'url_type'];

    // url_type 链接类型
    const URL_TYPE_ACTIVITY = 1; // 活动
    const URL_TYPE_PRODUCT = 2; // 商品

    public function position(){
        return $this->belongsTo(AdvPosition::class, 'adv_positions_id');
    }

    public static function getUrlTypes(){
        return [
            self::URL_TYPE_ACTIVITY => '活动',
            self::URL_TYPE_PRODUCT => '商品'
        ];
    }



}
