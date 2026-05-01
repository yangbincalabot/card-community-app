<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/16
 * Time: 16:58
 */

namespace App\Services;


use App\Models\CompanyBind;
use App\Models\User;

class CompanyBindService
{
    public function list(User $user){
        if($user->companyCardStatus === true && $user->companyCard){
            $companyBinds = CompanyBind::query()->where([
                'company_id' => $user->companyCard->id
            ])->with(['user', 'carte'])->latest()->paginate();
            return $companyBinds;
        }else {
            abort(403, '请升级后再操作');
        }
    }
}