<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/27
 * Time: 11:00
 */

namespace App\Services;
use App\Models\CompanyCard;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CompanyService
{
    public function getList(Request $request){
        $query = CompanyCard::query()->whereHas('user', function($query){
            $query->where('enterprise_at', '>=', Carbon::now());
        })->where('status', CompanyCard::TYPE_NORMAL)->companyNameNotEmpay();
        $appens = [];

        // 按公司名查找
        $company_name = $request->get('company_name');
        if($company_name && !empty($company_name)){
            $query->where('company_name', 'like', '%' . $company_name . '%');
            $appens['company_name'] = $company_name;
        }

        $companies = $query->latest()->paginate()->appends($appens);
        return $companies;
    }
}