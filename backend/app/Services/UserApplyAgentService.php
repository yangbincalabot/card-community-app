<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22
 * Time: 11:28
 */

namespace App\Services;

use App\Models\Area;
use App\Models\User\UserApplyAgent;


class UserApplyAgentService
{
    public function add($user, $formData){
        $formData['status'] = UserApplyAgent::APPLY_STATUS_STAY;
        $userApplyAgent = $user->applyAgents()->create($formData);
        return $userApplyAgent;
    }


    private function getAddressInfo($province, $city, $district){
        return Area::whereIn('code', [
            $province, $city, $district
        ])->get()->toArray();
    }
}