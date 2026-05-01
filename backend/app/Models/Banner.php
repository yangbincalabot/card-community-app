<?php

namespace App\Models;

use App\Models\Traits\ImageUrlTrait;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use ImageUrlTrait;
    protected $table = 'banner';


    protected $fillable = [
        'title', 'image', 'sort', 'type', 'url', 'url_type'
    ];

    // type
    const HOME_BANNER_TYPE = "HOME_BANNER";  // 首页banner
    const COMMUNAL_BANNER_TYPE = "COMMUNAL_BANNER"; // 公共banner


    // url_type 链接类型
    const URL_TYPE_ACTIVITY = 1; // 活动
    const URL_TYPE_SUPPLY_DEMAND = 2; // 供需


    public static function getBannerTypes(){
        return [
            self::HOME_BANNER_TYPE => '首页banner',
            self::COMMUNAL_BANNER_TYPE => '公告banner',
        ];
    }

    public static function getUrlTypes(){
        return [
            self::URL_TYPE_ACTIVITY => '活动',
            self::URL_TYPE_SUPPLY_DEMAND => '供需'
        ];
    }

    public function getUrlAttribute($url){
        return $url ?: '';
    }


}
