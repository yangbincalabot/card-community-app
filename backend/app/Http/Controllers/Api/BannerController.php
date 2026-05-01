<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\BannerRequest;
use App\Http\Resources\BannerResource;
use App\Services\BannerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    // 获取指定类型的banner
    public function getBanner(BannerRequest $request, BannerService $bannerService){
        // 多个banner类型以|号隔开
        $banner_type = $request->get('type');
        return new BannerResource($bannerService->get($banner_type));
    }
}
