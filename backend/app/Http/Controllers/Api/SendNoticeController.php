<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SendCodeResource;
use App\Models\Carte;
use App\Models\Configure;
use App\Models\SmsNotice;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Facades\Sms;

class SendNoticeController extends Controller{
    public function sendNotice(Request $request){
        $user = $request->user();
        $data = $request->all();
        $phone = $data['phone'] ?? '';
        $name = $data['name'] ?? '';
        if (empty($user) || empty($data)) {
            abort(403,SmsNotice::ERROR_MSG);
        }
        if (empty($name) || empty($phone)) {
            abort(403,SmsNotice::ERROR_MSG);
        }
        $carteInfo = Carte::query()->where('phone', $phone)->where('uid', '<>', 0)->first();
        if (!empty($carteInfo)) {
            return new SendCodeResource(['status' => 0, 'msg' => '该用户已完善名片。']);
        }

        $checkNoticeRes = SmsNotice::checkSendFrequency($user['id']);
        if ($checkNoticeRes['status'] == 0) {
            return new SendCodeResource(['status' => 0, 'msg' => $checkNoticeRes['msg']]);
        }
        DB::beginTransaction();
        try{
            //模板参数
            $smsParam = array(
                'template' => config('sms.templates.aliyun.card_notice'),
                'data' => [
                    'name' => $name
                ],
            );
            // 正式
            $responseResult = Sms::send($phone, $smsParam);
            $sendResult = $responseResult['aliyun']['result'];

            if (isset($sendResult['Code']) && strtoupper($sendResult['Code']) === 'OK') {
                SmsNotice::addNotice($user['id'], $name, $phone);
                DB::commit();
                return new SendCodeResource(['status' => 1, 'msg' => '通知发送成功']);
            } else {
                DB::rollBack();
                return new SendCodeResource(['status' => 0, 'msg' => '通知发送失败']);
            }
        }catch (\Exception $exception){
            DB::rollBack();
            \Log::error('通知发送失败：' . $exception->getMessage());
            return new SendCodeResource(['status' => 0, 'msg' => '通知发送失败']);
        }

    }
}
