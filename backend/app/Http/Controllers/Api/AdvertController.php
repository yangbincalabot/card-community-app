<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AdvertResource;
use App\Services\AdvertService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdvertController extends Controller
{
    // 获取指定广告位置的图片
    public function getAdver(Request $request, AdvertService $advertService){
        // 多个广告位置以|号隔开
        $adv_position_name = $request->get('positions');
        return new AdvertResource($advertService->get($adv_position_name));
    }
}
