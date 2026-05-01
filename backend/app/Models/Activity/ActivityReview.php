<?php

namespace App\Models\Activity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityReview extends Model
{
    protected $table = 'activity_review';
    protected $fillable = ['user_id', 'cover_image', 'title', 'content','type'];

    const STATUS_ONE = 1;
    const STATUS_TWO = 2;
    const STATUS_THREE = 3;

    const TYPE_ONE = 1;
    const TYPE_TWO = 2;

    // 验证状态
    const CHECK_STATUS_ONE = 1;
    const CHECK_STATUS_TWO = 2;
    const CHECK_STATUS_THREE = 3;

    public function getStatus($type = '') {
        $data = [
            self::STATUS_ONE => '审核中',
            self::STATUS_TWO => '通过',
            self::STATUS_THREE => '拒绝'
        ];
        return $data[$type] ?? $data;
    }

    public function getType($type = '') {
        $data = [
            self::TYPE_ONE => '草稿',
            self::TYPE_TWO => '已发布'
        ];
        return $data[$type] ?? $data;
    }

    public function getCheckStatus ($type='') {
        $data = [
            self::CHECK_STATUS_ONE => '正常',
            self::CHECK_STATUS_TWO => '请登录',
            self::CHECK_STATUS_THREE => '您没有权限创建此项内容'
        ];
        return $data[$type] ?? $data;
    }

    public function setCoverImageAttribute($cover_image)
    {
        if (Str::startsWith($cover_image, ['http://', 'https://'])) {
            $this->attributes['cover_image'] = $cover_image;
            return true;
        }
        $new_image = '/storage/'.$cover_image;
        $this->attributes['cover_image'] = $new_image;
    }

    public function getCoverImageAttribute($cover_image)
    {
        if (Str::startsWith($cover_image, ['http://', 'https://'])) {
            return $cover_image;
        }
        $cover_image = config('app.url').$cover_image;
        return $cover_image;
    }
}
