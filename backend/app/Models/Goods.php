<?php

namespace App\Models;

use App\Models\Traits\ImagesTrait;
use App\Models\Traits\ImageUrlTrait;
use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    use ImagesTrait, ImageUrlTrait;
    protected $guarded = ['id'];

    // 是否显示
    const IS_SHOW_TRUE = true;
    const IS_SHOW_FALSE = false;

    protected $casts = [
        'is_show' => 'boolean',
        'images' => 'array'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(){
        return $this->belongsTo(CompanyCard::class, 'cid');
    }
}
