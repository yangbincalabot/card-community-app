<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\ImageUrlTrait;

class Bank extends Model
{
    use SoftDeletes, ImageUrlTrait;
    protected $fillable = ['name', 'is_use', 'image'];
    protected $casts = [
        'is_use' => 'boolean'
    ];

    const IS_USER_TRUE = true;
    const IS_USER_FALSE = false;


    /**
     * 有效银行卡
     * @@param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsUse($query){
        return $query->where('is_use', self::IS_USER_TRUE);
    }

}
