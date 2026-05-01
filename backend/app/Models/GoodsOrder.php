<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GoodsOrder extends Model
{
    protected $guarded = ['id'];

    // 是否支付
    const IS_PAY_FALSE = false;
    const IS_PAY_TRUE = true;

    protected $casts = [
        'is_pay' => 'boolean',
    ];

    protected $dates = ['payed_at'];


    public static function addLog($user_id, $goods_id, $price, $order_sm){
        $addData = compact('user_id', 'goods_id', 'price', 'order_sm');
        if(bccomp($price, 0.00, 2) === 0){
            $addData['is_pay'] = self::IS_PAY_TRUE;
            $addData['payed_at'] = Carbon::now();
        }
        return self::query()->create($addData);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function goods(){
        return $this->belongsTo(Goods::class, 'goods_id');
    }
}
