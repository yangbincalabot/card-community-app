<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CompanyResource;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CompanyCard;
use App\Models\User;
use App\Services\CompanySupplyService;

class CompanyController extends Controller
{

    public function index(Request $request, CompanyService $service){
        $companies = $service->getList($request);
        return new CompanyResource($companies);
    }

    // 企业详情
    public function detail(Request $request){
        $company_id = $request->get('company_id');
        $companyCard = CompanyCard::findOrFail($company_id);
        // 检查企业状态
        if($companyCard->status !== CompanyCard::TYPE_NORMAL){
            abort(403, '非法访问');
        }
        // 用户已经登录了...
        if (auth('api')->check()) {
            $user  = auth('api')->user();
            if ($user->id != $companyCard->uid) {
                $companyCard->increment('visits');
            }
        }
        $companyCard->load('user');
        return new CompanyResource($companyCard);
    }

    // 企业详情下的供需列表
    public function companySupply(Request $request, CompanySupplyService $service){
        $company_id = $request->get('company_id');
        $companyCard = CompanyCard::findOrFail($company_id);
        // 检查企业状态
        if($companyCard->status !== CompanyCard::TYPE_NORMAL){
            abort(403, '非法访问');
        }

        // 获取该公司下的所有供需
        $companySupply = $service->getCompanySupply($companyCard->id);
        return new CompanyResource($companySupply);
    }
}
