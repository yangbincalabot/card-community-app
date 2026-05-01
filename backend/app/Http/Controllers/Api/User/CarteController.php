<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\UpdateCarteRequest;
use App\Http\Resources\CarteResource;
use App\Libraries\Creators\CarteCreator;
use App\Models\Carte;
use App\Models\CarteVisits;
use App\Models\CompanyBind;
use App\Models\CompanyCard;
use App\Models\ReceiveCarte;
use App\Models\Tag;
use App\Models\User\Attention;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CarteController extends Controller
{
    // 获取当前用户名片
    public function getCarteInfo(Request $request){
        Carte::unBind($request->user());
        $user = $request->user()->load(['carte' => function($query){
            $query->with('industry')->with('company_card');
        }, 'tags' => function($query){
            $query->where('type', Tag::TYPE_OWN);
        }]);
        $bind = null;
        if($user->carte){
            $bind = CompanyBind::query()->where([
                'uid' => $user->id,
                'carte_id' => $user->carte->id
            ])->with(['company' => function($query){
                $query->select(['id', 'company_name']);
            }])->latest()->first();
        }
        return new CarteResource(compact('user', 'bind'));
    }

    // 编辑当前用户名片
    public function updateCarte(UpdateCarteRequest $request, CarteCreator $creator){
        $cate = $creator->updateOrCreate($request);
        return new CarteResource($cate);
    }

    // 根据id查看名片信息
    public function getCarteNews(Request $request) {
        $id = $request->input('id', '');
        if (empty($id)) {
            return new CarteResource(collect());
        }
        $result = Carte::where('id', $id)->select(['id', 'uid', 'name', 'company_name'])->first();
        if (empty($result)) {
            return new CarteResource(collect());
        }
        return new CarteResource($result);

    }

    // 根据id增加分享名片次数
    public function addShareNum(Request $request) {
        $id = $request->input('id', '');
        if (empty($id)) {
            return new CarteResource(collect());
        }
        Carte::where('id', $id)->increment('share_num');
        return new CarteResource(collect());
    }

    /*
     *  新增访客、访客总数、新增好友、
     *  好友总数、名片被分享、供需被分享、
     *  企业被分享、活动/会务被分享、企业被查看
     */
    // 名片页统计
    public function carteStatistical() {
        $uid = Auth::id();
        $carteInfo = Carte::query()->where('uid', $uid)->select('id', 'uid', 'name', 'visits', 'share_num', 'new_visits')->first();
        $carteNewVisits = $carteInfo['new_visits']; // 新增访问
        $carteTotalVisits = $carteInfo['visits']; // 访客总数
        $newApply = ReceiveCarte::query()->where(['user_id' => $uid, 'is_read' => ReceiveCarte::UNREAD])->count(); // 新增好友请求
        $friendTotal = Attention::query()->where(['uid' => $uid, 'status' => Attention::ATTENTION_STATUS_ONE])->count();  // 好友总数
        $carteShareNum = $carteInfo['share_num']; // 名片被分享
        $companyVisits = CompanyCard::query()->where('uid', $uid)->value('visits');  // 企业被查看

        // 本周访客
        $thisWeekVisits = CarteVisits::query()->where('carte_id', $carteInfo->id)->get()->sum(function ($visit){
            return $visit->views_this_week;
        });
        $statisticalArr = [];
        for ($i = 0; $i < 9; $i++) {
            switch ($i) {
                case 0:
                    $title = '新增访客';
                    $number = $carteNewVisits;
                    $weekNumber = 0;
                    break;
                case 1:
                    $title = '访客总数';
                    $number = $carteTotalVisits;
                    $weekNumber = $thisWeekVisits;
                    break;
                case 2:
                    $title = '新增好友请求';
                    $number = $newApply;
                    $weekNumber = 0;
                    break;
                case 3:
                    $title = '好友总数';
                    $number = $friendTotal;
                    $weekNumber = 0;
                    break;
                case 4:
                    $title = '名片被分享';
                    $number = $carteShareNum;
                    $weekNumber = 0;
                    break;
                case 5:
                    $title = '供需被分享';
                    $number = 0;
                    $weekNumber = 0;
                    break;
                case 6:
                    $title = '企业被分享';
                    $number = 0;
                    $weekNumber = 0;
                    break;
                case 7:
                    $title = '活动/会务被分享';
                    $number = 0;
                    $weekNumber = 0;
                    break;
                case 8:
                    $title = '企业被查看';
                    $number = $companyVisits;
                    $weekNumber = 0;
                    break;
            }
            $statisticalArr[$i]['title'] = $title;
            $statisticalArr[$i]['number'] = $number;
            $statisticalArr[$i]['weekNumber'] = $weekNumber;
        }
        return new CarteResource($statisticalArr);

    }

    // 重置新增访客数
    public function resetNewVisits() {
        $uid = Auth::id();
        if (empty($uid)) {
            return ;
        }
        Carte::query()->where('uid', $uid)->update(['new_visits' => 0]);
        return ;
    }
}
