<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\CommonResource;
use App\Models\Association;
use App\Services\AssociationsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class ApplicationSocietyController extends Controller
{

    protected $service;
    public function __construct(AssociationsService $service)
    {
        $this->service = $service;
    }

    // 微信支付
    public function wechatPay(Request $request){
        $formData = $request->all();
        $pay = $this->service->wechatPay($request->user(), $formData['aid'], $formData['reason'], $formData['type'],
            $formData['avatar'], $formData['carte_id'], $formData['role_id']);
        return new CommonResource($pay ?: collect());
    }

    // 余额支付
    public function balancePay(Request $request){
        $formData = $request->all();
        $this->service->balancePay($request->user(), $request->get('aid'), $request->get('cash_password'));
    }


    private function getAssociation(Request $request){
//        $association = Association::query()->where([
//            ''
//        ])
    }


    // 审核处理
    public function applicationCheck(Request $request){
        $isJoined = $this->service->checkJoined($request->user(), $request->get('aid'));
        return new CommonResource(compact('isJoined'));
    }


    // 支付回调
    public function wechatPayNotify(){
        return $this->service->wechatPayNotify();
    }

    // 退款回调
    public function wechatRefundNotify(){
        return $this->service->wechatRefundNotify();
    }


    // 申请入驻
    public function applicationSociety(Request $request){
        $formData = $request->all();
        $this->service->applicationSociety($request->user(), $formData['aid'], $formData['reason'], $formData['type'],
            $formData['avatar'], $formData['carte_id'], $formData['role_id']);
    }
}
