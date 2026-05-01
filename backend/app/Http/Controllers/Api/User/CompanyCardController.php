<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\UpdateCompanyCardRequest;
use App\Http\Resources\CompanyCardResource;
use App\Libraries\Creators\CompanyCardCreator;
use App\Models\CompanyCard;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyCardController extends Controller
{
    // 获取用户企业名片
    public function getCompanyCardInfo(Request $request){
        $user = $request->user();
        // 如果是企业会员，但没有企业名片，默认创建一条
        if($user->companyCardStatus === true && !$user->companyCard){
            CompanyCard::addDefaultCompanyCard($user->id, '', $user->avatar);
        }
        $user = $user->load(['companyCard' => function($query){
            $query->with('industry');
        }]);
       // $this->checkCompanyUser($user);
        return new CompanyCardResource($user);
    }

    // 编辑用户企业名片
    public function updateCompanyCard(UpdateCompanyCardRequest $request, CompanyCardCreator $cardCreator){
        $user = $request->user();
        $this->checkCompanyUser($user);
        $companyCard = $cardCreator->updateOrCreate($request);
        return new CompanyCardResource($companyCard);
    }

    protected function checkCompanyUser(User $user){
        if(!$user->isCompanyUser()){
            abort(403, '请升级企业会员');
        }
    }

}
