<?php

namespace App\Models;

use App\Exceptions\InternalException;
use App\Models\Product\ProductSkuCategory;
use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $fillable = ['title', 'description', 'stock','sku_category_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category(){
        return $this->belongsTo(ProductSkuCategory::class,'sku_category_id','id');
    }

    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('减库存不可小于0');
        }

        return $this->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }

    public function addStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('加库存不可小于0');
        }
        $this->increment('stock', $amount);
    }
}
