<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/19
 * Time: 10:51
 */

namespace App\Models\Traits;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


trait ImagesTrait
{
    public function setImagesAttribute($images)
    {
        // TODO 图片数组
        $images = is_array($images) ? $images : [$images];
        if (is_array($images)) {
            $newImages = [];
//            Log::info(json_encode($images));
            foreach ($images as $image){
                // 如果 image 字段本身就已经是完整的 url 就直接返回
                if (Str::startsWith($image, ['http://', 'https://'])) {
                    $newImages[] = $image;
                }else{
                    $newImages[] = Storage::disk('public')->url($image);
                }
            }

            $this->attributes['images'] = json_encode($newImages);
        }
    }

    public function getImagesAttribute($images)
    {
        $newImages = [];
        $oldImages = json_decode($images, true);
        if(!empty($oldImages)){
            foreach ($oldImages as $image){
                // 如果 image 字段本身就已经是完整的 url 就直接返回
                if (Str::startsWith($image, ['http://', 'https://'])) {
                    $newImages[] = $image;
                }else{
                    $newImages[] = Storage::disk('public')->url($image);
                }
            }
        }

        return $newImages;
    }
}