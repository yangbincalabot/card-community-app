<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/17
 * Time: 11:08
 */

namespace App\Services;


use App\Models\Banner;

class BannerService
{
    public function get($banner_type){
        $query = Banner::query();
        if(strpos($banner_type, '|') !== false){
            $query->whereIn('type', explode('|', $banner_type));
        }else{
            $query->where('type', $banner_type);
        }
        return $query->orderBy('sort', 'DESC')->orderBy('id', 'desc')->get();

    }
}