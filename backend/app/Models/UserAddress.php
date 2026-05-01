<?php

namespace App\Models;

use App\Models\Traits\AreaTrait;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use AreaTrait;
    protected $fillable = [
        'province',
        'city',
        'district',
        'address',
        'zip',
        'contact_name',
        'contact_phone',
        'last_used_at',
        'is_default',
        'full_address'
    ];
    protected $dates = ['last_used_at'];
    protected $casts = [
        'is_default' => 'boolean'
    ];

    const IS_DEFAULT_TRUE = true;
    const IS_DEFAULT_FALSE = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
