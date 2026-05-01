<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\IndexResource;
use App\Models\Activity\Activity;
use App\Models\Activity\ActivityApply;
use App\Models\Banner;
use App\Models\Communal;
use App\Models\ReceiveCarte;
use App\Services\AdvertService;
use App\Services\BannerService;
use App\Services\StoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

// 首页数据
class IndexController extends Controller
{
    public function index(Request $request, AdvertService $advertService, BannerService $bannerService, StoreService $storeService){
        $communal = Communal::latest()->first(); // 最新公告
        $homeAdv = $advertService->get('HOME_ADV'); // 首页广告图
        $homeBanner = $bannerService->get(Banner::HOME_BANNER_TYPE); // 首页轮播
        $stores = $storeService->getLatelyStore([
            'longitude' => $request->get('longitude'),
            'latitude' => $request->get('latitude')
        ], 3);
        $requestUser = '';
        if (auth('api')->check()) {
            // 用户已经登录了...
            $requestUser  = auth('api')->user();
        }
        $request_user = $requestUser;
        return new IndexResource(compact('communal', 'homeAdv', 'homeBanner', 'stores','request_user'));
    }

    // 首页相关内容提示数量 && 加上查看报名活动中是否有活动当天的活动
    public function getNavData() {
        $is_login = 0;
        $activity_num = 0;
        $new_carte_num = 0;
        $has_activity = false;
        $dayEventActivity = [];
        $user = [];
        if ($user = request()->user('api')) {
            // 用户已经登录了...
//            $user  = auth('api')->user();
            $activity_num = $this->getApplyActivityNum($user['id']);
            $new_carte_num = $this->getNewCarteNum($user['id']);
            $is_login = 1;
            $dayEventActivity = $this->getDayEventActivity($user['id']);
            if (!empty($dayEventActivity)) {
                $has_activity = true;
            }
        }
        return new IndexResource(compact('is_login', 'activity_num', 'new_carte_num', 'has_activity', 'dayEventActivity', 'user'));
    }

    //  查看报名活动中是否有活动当天的活动
    public function getDayEventActivity($uid) {
        $today = Carbon::today()->toDateTimeString();
        $tomorrow = Carbon::tomorrow()->toDateTimeString();
        $applyWhere['status'] = ActivityApply::STATUS_COMPLETED;
        $applyWhere['uid'] = $uid;
        $activity = ActivityApply::with(['activity'])->where(function ($query)  use ($today, $tomorrow) {
            $query->whereHas('activity', function ($query) use ($today, $tomorrow) {
                $query->whereBetween('activity_time', [$today, $tomorrow]);
            });
        })
            ->where($applyWhere)
            ->whereIn('refund_status', [ActivityApply::REFUND_STATUS_NOT, ActivityApply::REFUND_STATUS_REFUNDABLE])
            ->first();
        return $activity;
    }

    public function getApplyActivityNum($uid) {
        $today = Carbon::today()->toDateTimeString();
        $applyWhere['status'] = ActivityApply::STATUS_COMPLETED;
        $applyWhere['uid'] = $uid;
        $activity_num = ActivityApply::with(['activity'])->where(function ($query)  use ($today) {
            $query->whereHas('activity', function ($query) use ($today) {
                $query->where('activity_time', '>', $today);
            });
        })
            ->where($applyWhere)
            ->whereIn('refund_status', [ActivityApply::REFUND_STATUS_NOT, ActivityApply::REFUND_STATUS_REFUNDABLE])
            ->count();
        return $activity_num;
    }

    // 收到名片的数量
    protected function getNewCarteNum($user_id){
        $new_carte_num = ReceiveCarte::query()->where('user_id', '=', $user_id)
            ->where('is_read', ReceiveCarte::UNREAD)->count();
        return $new_carte_num;
    }

    public function checkLogin(){
        $is_login = false;
        if(auth('api')->check()){
            $is_login =  true;
        }

        return new IndexResource(compact('is_login'));
    }
}
