<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/26
 * Time: 10:12
 */

namespace App\Services;


use App\Models\CompanyCard;
use App\Models\Supply;

class CompanySupplyService
{

    // 找出某公司下的员工发布的供需
    public function getCompanySupply($company_id){
        $companySupply = [];
        // 验证公司的状态
        $companyCard = CompanyCard::find($company_id);
        if(!$companyCard || ($companyCard->status !== CompanyCard::TYPE_NORMAL)){
            return $companySupply;
        }
        // 关联到该公司的个人名片(员工)
        $cartes = $companyCard->cartes;
        if($cartes){
            $carteUserIds = $cartes->map(function($carte){
                return $carte->uid;
            })->toArray();
            $companySupply = Supply::query()->whereIn('uid',$carteUserIds)->where('status', Supply::STATUS_PASSED)->with([
                'carte',
                'self_like'
            ])->latest()->paginate();
        }
        if($companySupply){
            $companySupply = Supply::buildList($companySupply);
        }
        return $companySupply;
    }
}