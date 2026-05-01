<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\CompanyBindResource;
use App\Models\Carte;
use App\Models\CarteDepartment;
use App\Models\CompanyBind;
use App\Services\CompanyBindService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyBindController extends Controller
{
    protected $service;

    public function __construct(CompanyBindService $service)
    {
        $this->service = $service;
    }

    public function list(Request $request){
        $user = $request->user();
        $companyBinds = $this->service->list($user);
        return new CompanyBindResource(compact('companyBinds'));
    }

    // 绑定操作
    public function bindOperate(Request $request){
        $type = $request->get('type');
        $id = $request->get('id');
        $user = $request->user();
        $companyBind = CompanyBind::query()->findOrFail($id);
        if($user->id != $companyBind->company->uid){
            abort(403, '非法操作');
        }

        $operateTypes = [
            CompanyBind::AUDIT_SUCCESS_STATUS => 'agree',
            CompanyBind::AUDIT_FAILURE_STATUS => 'refuse',
        ];

        if(!in_array($type, ['agree', 'refuse'])){
            abort(403, '参数错误');
        }

        if($type === 'agree'){
            // 修改用户的绑定
            if(!$companyBind->carte){
                abort(404, '名片不存在');
            }
            $companyBind->carte->cid = $companyBind->company->id;
            // 将绑定公司名称以及地址赋值给绑定用户
            $companyBind->carte->company_name = $companyBind->company->company_name;
            $companyBind->carte->longitude = $companyBind->company->longitude;
            $companyBind->carte->latitude = $companyBind->company->latitude;
            $companyBind->carte->address_title = $companyBind->company->address_title;
            $companyBind->carte->address_name = $companyBind->company->address_name;

            $companyBind->carte->save();

            $companyUser = $companyBind->company->user;
            CarteDepartment::query()->where('carte_id', $companyBind->carte_id)->where('uid', '<>', $companyUser->id)->delete();
        }

        $status = array_search($type, $operateTypes);
        $companyBind->status = $status;
        $companyBind->save();
        return new CompanyBindResource(['ok']);
    }

    public function checkUserBind(Request $request){
        $id = $request->get('id');
        $companyBind = CompanyBind::query()->findOrFail($id);
        $userId = $companyBind->uid;
        // 获取用户最后绑定的公司
        $userLastBind = CompanyBind::query()->where('uid', $userId)->latest()->first();
        $status = false;
        if($userLastBind->company_id !== $companyBind->company_id){
            $status = true;
        }
        return new CompanyBindResource(compact('status'));
    }
}
