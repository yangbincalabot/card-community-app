<?php

use Illuminate\Database\Seeder;

class CompanyCardRolesSeeder extends Seeder
{
    /**
     * 主要将之前开通企业会员的公司，加入到默认协会中
     *
     * @return void
     */
    public function run()
    {
        $companyIds = \App\Models\CompanyCardRole::query()->pluck('company_id')->toArray();
        $hasNotCompanyIds = \App\Models\CompanyCard::query()->whereNotIn('id', $companyIds)->where('company_name', '<>', '')->pluck('id')->toArray();
        if (!empty($hasNotCompanyIds)){
            $addData = [];
            $platformAssociation = \App\Models\Association::query()->where('user_id', 0)->first();
            $roleId = \App\Models\CompanyRole::query()->where('aid', $platformAssociation->id)->orderBy('sort', 'desc')->value('id');
            $roleSort = \App\Models\CompanyCardRole::query()->where(['aid' => $platformAssociation->id, 'role_id' => $roleId])->max('role_sort');
            foreach ($hasNotCompanyIds as $key => $companyId){
                $sort = $roleSort + $key + 1;
                $addData[] = [
                    'company_id' => $companyId,
                    'role_id' => $roleId,
                    'role_sort' => $sort,
                    'aid' => $platformAssociation->id,
                ];
            }

            \App\Models\CompanyCardRole::query()->insert($addData);
        }
    }
}
