<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\UserApplyAgentResource;
use App\Models\User\UserApplyAgent;
use App\Services\UserApplyAgentService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Medz\IdentityCard\China\Identity;
use App\Models\Configure;
use App\Models\Store;
use App\Models\User;

class UserApplyAgentController extends Controller
{


    // 提交申请
    public function add(Request $request, UserApplyAgentService $applyAgentService){

        $is_pass = $this->checkIdCard($request->get('id_card'));
        if(!$is_pass){
            abort(403, '身份证有误');
        }


        if($request->get('agent_id') >= 3 && $this->checkFacilitator($request->get('city'), $request->get('district'))){
            abort(403, '该区域已有服务商');
        }

        $check_has_stay = $request->user()->applyAgents()->whereIn('status', [UserApplyAgent::APPLY_STATUS_STAY, UserApplyAgent::APPLY_STATUS_SUCCESS])->first();
        if($check_has_stay){
            abort(403, '非法操作');
        }

        $formData = [
            'address' => $request->get('address'),
            'agent_id' => $request->get('agent_id'),
            'city' => $request->get('city'),
            'district' => $request->get('district'),
            'id_card' => $request->get('id_card'),
            'mobile' => $request->get('mobile'),
            'name' => $request->get('name'),
            'province' => $request->get('province')
        ];
        $userApplyAgent = $applyAgentService->add($request->user(), $formData);
        return new UserApplyAgentResource($userApplyAgent);
    }


    // 检查用户是否有未审核和审核成功的记录
    public function checkHasStay(Request $request){
        $userApplyAgent = $request->user()->applyAgents()->whereIn('status', [UserApplyAgent::APPLY_STATUS_STAY, UserApplyAgent::APPLY_STATUS_SUCCESS])->first();
        return new UserApplyAgentResource($userApplyAgent);
    }

    private function checkIdCard ($id_card) {
        $peopleIdentity = new Identity($id_card);
        $peopleRegion = $peopleIdentity->legal();
        return $peopleRegion;
    }


    // 判断所在区域是否有服务商
    private function checkFacilitator($city, $district){
        $condition = [];
        $configure = Configure::where('name', 'ADMINISTRATIVE_DIVISION')->pluck('value', 'name')->toArray();
        // 按市
        if(isset($configure['ADMINISTRATIVE_DIVISION']) && $configure['ADMINISTRATIVE_DIVISION'] == Configure::ADMINISTRATIVE_DIVISION_CITY){
            $condition['city'] = $city;
        }else{
            // 按区县
            $condition['district'] = $district;
        }
        $count = Store::where($condition)->whereHas('user', function($query){
            $query->where('type', User::USER_TYPE_FOUR);
        })->count();
        return (bool) $count;
    }
}
