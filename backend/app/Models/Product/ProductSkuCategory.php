<?php

namespace App\Models\Product;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class ProductSkuCategory extends Model
{
    protected $table = 'product_sku_categories';     // 数据表名
    public static $snakeAttributes = false;   // 设置关联模型在打印输出的时候是否自动转为蛇型命名
    protected $guarded = ['id'];        // 过滤的字段

    public function category(){
        return $this->belongsTo(Category::class,'category_id','id');
    }
}
