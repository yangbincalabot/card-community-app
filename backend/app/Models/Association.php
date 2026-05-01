<?php

namespace App\Models;

use App\Models\Traits\ImagesTrait;
use App\Models\Traits\ImageUrlTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Association extends Model
{
    use ImageUrlTrait, ImagesTrait, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'images' => 'array',
        'service_images' => 'array',
        'member_wall' =>'array'
    ];

    protected $appends = [
        'status_text'
    ];


    // 状态
    const STATUS_NOT_REVIEWED = 0;  // 未审核
    const STATUS_FAILURE = 1; // 审核失败
    const STATUS_SUCCESS = 2; // 审核成功

    // 上级协会审核
    const PAT_SUCCESS = 1; // 审核成功
    const PAT_UNDER_REVIEW = 2; // 审核中
    const PAT_FAIL = 3; // 审核失败

    const STATUS_TEXT = [
        self::STATUS_NOT_REVIEWED => '未审核',
        self::STATUS_FAILURE => '审核失败',
        self::STATUS_SUCCESS => '审核成功',
    ];


    // 获取平台协会
    public static function getPlatform(){
        return self::query()->where('user_id', 0)->get();
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(){
        return $this->belongsTo(CompanyCard::class, 'cid');
    }

    public function getStatusTextAttribute(){
        $text = '';
        if (isset($this->attributes['status'])){
            $text = self::STATUS_TEXT[$this->attributes['status']] ?? '';
        }

        return $text;
    }

    public function associations(){
        return $this->hasMany(CompanyCardRole::class, 'aid');
    }



    public function setServiceImagesAttribute($images)
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

            $this->attributes['service_images'] = json_encode($newImages);
        }
    }

    public function getServiceImagesAttribute($images)
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

    public function getMemberWallAttribute($images) {
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

    public function setMemberWallAttribute($images) {
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

            $this->attributes['member_wall'] = json_encode($newImages);
        }
    }

    public function roles() {
        return $this->hasMany(CompanyRole::class, 'aid');
    }
}
