<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 20:24
 */

namespace App\Models\Traits;
use Illuminate\Support\Str;


trait ImageUrlTrait
{
    // 拼接图片地址
    public function getImageAttribute($value){
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }
        return \Storage::disk('public')->url($value);
    }
}