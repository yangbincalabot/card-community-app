<?php

namespace App\Models\User;

use App\Models\Association;
use App\Models\CompanyCard;
use Illuminate\Database\Eloquent\Model;

class FootPrint extends Model
{
    protected $guarded = ['id'];

    public function association(){
        return $this->belongsTo(Association::class, 'aid');
    }

    public function company() {
        return $this->belongsTo(CompanyCard::class, 'company_id');
    }
}
