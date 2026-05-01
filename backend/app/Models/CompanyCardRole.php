<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCardRole extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    const IS_COMPANY_TRUE = true;
    const IS_COMPANY_FALSE = false;

    protected $casts = [
        'is_company' => 'boolean'
    ];
}
