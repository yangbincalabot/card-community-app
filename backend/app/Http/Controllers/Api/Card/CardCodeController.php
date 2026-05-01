<?php

namespace App\Http\Controllers\Api\Card;

use App\Http\Requests\ScanCodeRequest;
use App\Http\Resources\CardCodeResource;
use App\Libraries\Creators\AttentionCreator;
use App\Libraries\Creators\ReceiveCarteCreator;
use App\Models\Carte;
use App\Models\Configure;
use App\Models\ReceiveCarte;
use App\Models\ScanFiles;
use App\Models\ScanLog;
use App\Models\User;
use App\Models\User\Attention;
use App\Services\ScanCardService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CardCodeController extends Controller
{

    // 扫描小程序码
    public function scanCode(ScanCodeRequest $request, ReceiveCarteCreator $receiveCarteCreator, AttentionCreator $AttentionCreator){
        $user = $request->user();
        $formData = $request->all();
        $formData['is_scan'] = true;
        $formData['is_adding'] = ReceiveCarte::BY_ADDING;

        // 名片扫码type只有1和2
        if(isset($formData['exchange_type']) && $formData['exchange_type'] == Attention::EXCHANGE_TYPE_THREE){
            $formData['type'] = ReceiveCarte::TYPE_SHARE;
        }else{
            $formData['type'] = ReceiveCarte::TYPE_SCAN;
        }

        // 收到的名片
        $receiveCarteCreator->addReceiveCarte($formData, $user->id);

        // 名片夹处理，双方都收藏
        // 当前用户(扫描主)
        $AttentionCreator->addCard(array_merge($formData, ['to_uid' => $formData['from_user_id']]), $user->id);

        // 名片本人(被扫描)
        $AttentionCreator->addCard(array_merge($formData, ['to_uid' => $user->id]), $formData['from_user_id']);
        Attention::setContactDefault($user->id, $formData['from_user_id']);
        $fromUserInfo = Carte::where('uid', $formData['from_user_id'])->first();
        return new CardCodeResource($fromUserInfo);

    }

    // 扫描名片
    public function scanCard(Request $request, ScanCardService $service){
        // 当日扫描次数判断
        $this->checkTodayScanNums($request->user()->id);
        $user = $request->user();

        // 获取小程序传递过来的名片图片
        $file = $request->file('file');


        $type = $request->get('type');
        if($file){
            // 解析结果
            ScanFiles::addScanFile($user->id, $file->getRealPath());
            $scanResult = $service->resolveCard($file->getRealPath());

            switch ($type){
                case 'normal':
                    // 首页进入OCR扫描或打开相册时$isOcr为true, 其它为false
                    // 用户名片信息完善时，保存数据uid=0的数据到数据库中
                    if($user->perfect == User::COMPLETE_PERFECTION){
                        if($scanResult['phone']){
                            $this->saveOther($scanResult, $user);
                        }
                    }
                    break;
                case 'self':

                    break;
                case 'other':
                    if($scanResult['phone']){
                        $this->saveOther($scanResult, $user);
                    }
                    break;
            }
            return new CardCodeResource($scanResult);
        }else{
            abort(403, '非法操作');
        }
    }

    // 保存其它人信息
    private function saveOther($scanResult, $user){
        $carte = Carte::query()->where('phone', $scanResult['phone'])->where('uid', '<>', 0)->first();
        if(empty($carte)){
            $scanResult['avatar'] = $scanResult['avatar'] ?? getletterAvatar($scanResult['name']);
            $carte = Carte::query()->updateOrCreate(['uid' => 0, 'phone' => $scanResult['phone']], $scanResult);
        }
        Attention::query()->updateOrCreate(['uid' => $user->id, 'from_id' => $carte->id], [
            'status' => Attention::ATTENTION_STATUS_ONE,
            'initial' => getInitial($scanResult['name']),
            'exchange_type' => Attention::EXCHANGE_TYPE_TWO,
        ]);
        Attention::setContactDefault($user->id, $carte->id);
    }

    // 扫描名片添加默认数据
    public function scanCardSave(Request $request, ScanCardService $service){
        $user = $request->user();
        // 当日扫描次数判断
        $this->checkTodayScanNums($user->id);
        // 判断用户是否有名片
        if($user->carte){
            return new CardCodeResource($user);
        }
        // 获取小程序传递过来的名片图片
        $file = $request->file('file');
        if($file){
            ScanFiles::addScanFile($user->id, $file->getRealPath());
            // 解析结果
            $scanResult = $service->resolveCard($file->getRealPath());
            $user->carte()->create([
                'name' => $scanResult['name'],
                'phone' => $scanResult['phone'],
                'address_title' => $scanResult['address_title'],
                'company_name' => $scanResult['company_name'],
                'email' => $scanResult['email'],
                'position' => $scanResult['position'],
                'province' => $scanResult['province'],
                'avatar' => $user->avatar ?$user->avatar:getletterAvatar($scanResult['name']),
                'city' => $scanResult['city'],
                'longitude' => $scanResult['longitude'],
                'latitude' => $scanResult['latitude']
            ]);
            return new CardCodeResource($user);
        }else{
            abort(403, '非法操作');
        }
    }

    private function checkTodayScanNums($user_id){
        $scanCardLimit = Configure::getValue('SCAN_NUMS', 10);
        $todayScanNums = ScanLog::getTodayScanNums($user_id);
        if($scanCardLimit != 0 && $todayScanNums > $scanCardLimit){
            abort(403, '今日扫描超出限制');
        }
    }
}
