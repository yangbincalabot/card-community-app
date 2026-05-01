<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL = 'seckill';
    const TYPE_PROMOTION = 'promotion';

    public static $typeMap = [
        self::TYPE_NORMAL  => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
        self::TYPE_SECKILL => '秒杀商品',
        self::TYPE_PROMOTION => '促销商品',
    ];

    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count',
        'price',
        'original_price',
        'cost_price' ,
        'yjdl_price',
        'qydl_price',
        'qyfws_price',
        'type',
        'long_title', // 添加 long_title 到 $fillable 属性中
        'images',
    ];
    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];

    protected $appends = ['exclusive_price'];

    public function getExclusivePriceAttribute($value)
    {
        $exclusive_price = $this->price;
        // 如果有登录，就根据会员类型返回对应的价格
        if (auth('api')->check()) {
            // 用户已经登录了...
            $user  = auth('api')->user();
            $user_type = $user->type;
            switch ($user_type){
                case User::USER_TYPE_ONE:
                    $exclusive_price = $this->price;
                    break;
                case User::USER_TYPE_TWO:
                    $exclusive_price = $this->yjdl_price;
                    break;
                case User::USER_TYPE_THREE:
                    $exclusive_price = $this->qydl_price;
                    break;
                case User::USER_TYPE_FOUR:
                    $exclusive_price = $this->qyfws_price;
                    break;
            }
        }
        // 如果没有登录，就返回售价
        return $exclusive_price;
    }

    public function setDescriptionAttribute($description)
    {
        $html = preg_replace( '/(<img.*?)(style=.+?[\'|"])|((width)=[\'"]+[0-9]+[\'"]+)|((height)=[\'"]+[0-9]+[\'"]+)/i', '$1' , $description);
        $res = str_replace( '<img ', '<img style="width:100%;height:auto" ' , $html);
        $this->attributes['description'] = $res;
    }


    public function setImageUrlAttribute($image)
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }
        return Storage::disk('public')->url($image);
    }

    public function getImageAttribute()
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
        return Storage::disk('public')->url($this->attributes['image']);
    }

    public function setImagesAttribute($images)
    {
        if (is_array($images)) {
            $newImages = [];
            Log::info(json_encode($images));
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



    public function scopeByIds($query, $ids)
    {
        return $query->whereIn('id', $ids)->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $ids)));
    }

    // 与商品SKU关联
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }

    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }

    public function seckill()
    {
        return $this->hasOne(SeckillProduct::class);
    }

    public function getImageUrlAttribute()
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
        return Storage::disk('public')->url($this->attributes['image']);
    }

    public function getGroupedPropertiesAttribute()
    {
        return $this->properties
            // 按照属性名聚合，返回的集合的 key 是属性名，value 是包含该属性名的所有属性集合
            ->groupBy('name')
            ->map(function ($properties) {
                // 使用 map 方法将属性集合变为属性值集合
                return $properties->pluck('value')->all();
            });
    }

    public function toESArray()
    {
        // 只取出需要的字段
        $arr = Arr::only($this->toArray(), [
            'id',
            'type',
            'title',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);

        // 如果商品有类目，则 category 字段为类目名数组，否则为空字符串
        $arr['category'] = $this->category ? explode(' - ', $this->category->full_name) : '';
        // 类目的 path 字段
        $arr['category_path'] = $this->category ? $this->category->path : '';
        // strip_tags 函数可以将 html 标签去除
        $arr['description'] = strip_tags($this->description);
        // 只取出需要的 SKU 字段
        $arr['skus'] = $this->skus->map(function (ProductSku $sku) {
            return Arr::only($sku->toArray(), ['title', 'description', 'price']);
        });
        // 只取出需要的商品属性字段
        $arr['properties'] = $this->properties->map(function (ProductProperty $property) {
            // 对应地增加一个 search_value 字段，用符号 : 将属性名和属性值拼接起来
            return array_merge(array_only($property->toArray(), ['name', 'value']), [
                'search_value' => $property->name.':'.$property->value,
            ]);
        });

        return $arr;
    }
}
