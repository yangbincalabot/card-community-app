<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBind extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    const NOT_REVIEWED_STATUS = 0; // 未审核
    const AUDIT_SUCCESS_STATUS = 1; // 审核成功
    const AUDIT_FAILURE_STATUS = 2; // 审核失败

    public static function addCompanyBind($uid, $company_id, $carte_id,  $status = 0){
        $baseData = [
            'uid' => $uid,
            'company_id' => $company_id,
            'carte_id' => $carte_id,
            'status' => $status
        ];
        if($status === 0){
            return self::query()->firstOrCreate($baseData, $baseData);
        }else{
            return self::query()->create($baseData);
        }

    }

    public function company(){
        return $this->belongsTo(CompanyCard::class, 'company_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'uid');
    }

    public function carte(){
        return $this->belongsTo(Carte::class, 'carte_id');
    }
}
