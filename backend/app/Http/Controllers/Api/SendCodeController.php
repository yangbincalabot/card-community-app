<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SmsCodeRequest;
use App\Http\Resources\SendCodeResource;
use App\Models\Configure;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Facades\Sms;

class SendCodeController extends Controller{
    public function smsCode(SmsCodeRequest $request){
        $user = $request->user();
        $code = getSmsCode(6);
        // 查看是否开启验证码功能
        if(intval(Configure::getValue('SMS_SWITCH')) === Configure::SMS_OPEN){
            $userCarte = $user->carte;
            $phone = '';
            if($userCarte && $userCarte->phone){
                $phone = $userCarte->phone;
            }else{
                $phone = $user->phone;
            }
            if(empty($phone)){
                abort(403,'请先获取手机号再进行此操作');
            }
            $todayLimit = Configure::getValue('SMS_NUMS', 10);
            $todaySendNums = VerificationCode::getTodaySmsNums($user->id);
            if($todayLimit != 0 && $todaySendNums > $todayLimit){
                abort(403, '今日已超过发送限制');
            }

            // 发送频率限制(分钟)
            if(!VerificationCode::checkSendRate($user->id)){
                abort(403, '操作频繁，请稍后再试');
            }
            DB::beginTransaction();
            try{
                VerificationCode::create([
                    'user_id' => $user->id,
                    'channel' => VerificationCode::CHANNEL_SMS,
                    'account' => $phone,
                    'code' => $code
                ]);
                //模板参数
                $smsParam = array(
                    'template' => config('sms.templates.aliyun.cash_password'),
                    'data' => [
                        'code' => $code
                    ],
                );

                // 区别测试环境和线上环境
                $is_audit = intval(Configure::getValue('IS_AUDIT', 2));
                $successMsg = '发送成功'; // 后缀
                if($is_audit === Configure::IS_AUDIT_NO){
                    // 正式
                    $responseResult = Sms::send($phone, $smsParam);
                    $sendResult = $responseResult['aliyun']['result'];
                }else{
                    // 测试
                    $sendResult['Code'] = 'OK';
                    $successMsg = '验证码：' . $code;
                }


                if (isset($sendResult['Code']) && strtoupper($sendResult['Code']) === 'OK') {
                    DB::commit();
                    return new SendCodeResource(['status' => 1, 'msg' => $successMsg]);
                } else {
                    DB::rollBack();
                    return new SendCodeResource(['status' => 0, 'msg' => '一分钟之内只能发一次,或者您发送的次数已经达到上限']);
                }
            }catch (\Exception $exception){
                DB::rollBack();
                \Log::error('短信发送失败：' . $exception->getMessage());
                abort(403, $code);
//                \Log::error('短信发送失败：', $exception->getMessage());
//                abort(403, '短信发送失败:');
            }
        }

    }
}
