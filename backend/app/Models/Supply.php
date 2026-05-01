<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yansongda\Pay\Log;

class Supply extends Model
{
    protected $table = 'supply_demand'; // 供需表

    protected $fillable = ['uid', 'type', 'content', 'images','visits', 'likes', 'status'];

    protected $appends = ['type_name', 'short_content'];

    const STATUS_PASSED = 1; // 通过
    const STATUS_UNDER_REVIEW = 2; // 审核中
    const STATUS_NOT_PASS = 3; // 未通过
    const STATUS_DELETED = 99; // 已删除

    public function getStatus ($type='') {
        $data = [
            self::STATUS_PASSED => '通过',
            self::STATUS_UNDER_REVIEW => '审核中',
            self::STATUS_NOT_PASS => '未通过',
            self::STATUS_DELETED => '已删除'
        ];
        return $data[$type] ?? $data;
    }

    public function setImagesAttribute($images)
    {
        if (is_array($images)) {
            $newImages = [];
            foreach ($images as $image){
                // 如果 image 字段本身就已经是完整的 url 就直接返回
                if (Str::startsWith($image, ['http://', 'https://'])) {
                    $newImages[] = $image;
                }else{
                    $newImages[] = Storage::disk('public')->url($image);
                }
            }
            $this->attributes['images'] = json_encode($newImages);
            return $this->attributes['images'];
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

    public function getTypeNameAttribute()
    {
        $sdType = new SdType();
        $type_name = $sdType->getParentType($this->type,1);
        return $type_name ?? '未知';
    }

    public function getCarte($uid) {
        $carte = new Carte();
//        $where['open'] = $carte::OPEN_STATUS_PUBLIC;
        $where['uid'] = $uid;
        $result = $carte->where($where)->select('id','uid','name','company_name','phone','position','avatar')->first();
        return $result;
    }

    public function getCreatedAtAttribute($time)
    {
        return substr($time,0,16);
    }

    public function carte() {
        return $this->hasOne(Carte::class,'uid','uid')->select('id','uid','name','company_name','phone','position','avatar');
    }

    public function like() {
        return $this->hasMany(Like::class,'info_id')->where('status',Like::STATUS_ONE);
    }

    public function self_like() {
        // 有可能用户没有登录
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $uid = $user->id;
        }
        // 自义定一个不存在的uid
        $uid = $uid ?? 0;
        return $this->hasone(Like::class,'info_id')->where(['uid' => $uid, 'status' => Like::STATUS_ONE]);
    }

    // 后期可能删除，方便未登录以及已登录的用户点赞及取消点赞
    public static function buildList ($result) {
        foreach ($result as $value) {
            if ($value->self_like) {
                $value->likeStatus = true;
            } else {
                $value->likeStatus = false;
            }
        }
        return $result;
    }

    // 详细内容简介
    public function getShortContentAttribute(){
        return msubstr($this->attributes['content'], 0, 30);
    }

}
