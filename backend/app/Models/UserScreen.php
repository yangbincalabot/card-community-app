<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserScreen extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'area' => 'array',
        'industry' => 'array'
    ];

    // 是否激活状态
    const ACTIVE_TRUE = true;
    const ACTIVE_FALSE = false;

    // 是否认证
    const AUTHENTICATE_TRUE = true;
    const AUTHENTICATE_FALSE = false;

    public function industry(){
        return $this->belongsTo(Industry::class, 'industry_id');
    }

}
