<?php

namespace App\Http\Controllers\Api\Card;

use App\Http\Requests\UpdateCarteRequest;
use App\Http\Resources\CarteResource;
use App\Libraries\Creators\CarteCreator;
use App\Models\Carte;
use App\Models\CompanyBind;
use App\Models\User\Attention;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CreateCardController extends Controller
{

    public function info(Request $request){
        // 0 表示创建
        $id = $request->get('id');
        $carte = Carte::query()->where(['id' => $id, 'uid' => 0])->first();
        $user = $request->user();

        // 公司创建者不存在，解除绑定
        if($carte && (!$carte->company_card || !$carte->company_card->user)){
            $carte->cid = 0;
            $carte->save();
            // 重新获取数据，否则还有关联信息
            $carte = Carte::query()->where(['id' => $id, 'uid' => 0])->first();

            $attention = Attention::query()->where([
                'uid' => $user->id,
                'from_id' => $carte->id
            ])->first();
            if(!$attention){
                abort(403, '非法操作');
            }
        }


        // 绑定公司的信息
        $bind = null;
        if($carte){
            $bind = CompanyBind::query()->where([
                'uid' => 0,
                'carte_id' => $carte->id
            ])->with(['company' => function($query){
                $query->select(['id', 'company_name']);
            }])->latest()->first();
        }
        return new CarteResource(compact('carte', 'user', 'bind'));
    }

    // 手动创建名片
    public function create(UpdateCarteRequest $request, CarteCreator $carteCreator){
        $cate = $carteCreator->updateOrCreateOther($request);
        return new CarteResource($cate);
    }
}
