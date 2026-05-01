<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\SetCashPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Configure;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class UserSettingController extends Controller
{

    // 设置支付密码
    public function setCashPassword(SetCashPasswordRequest $request){
        $user = $request->user();
        $verificationCodes = null;
        if(intval(Configure::getValue('SMS_SWITCH')) === Configure::SMS_OPEN){
            $sms_code = $request->get('sms_code');
            if(empty($sms_code)){
                abort(403, '请输入验证码');
            }

            $verificationCodes = VerificationCode::getLatestSmsCode($user->id);
            if(!$verificationCodes){
                abort(403, '非法操作');
            }


            if(Carbon::now()->gt($verificationCodes->created_at->addMinutes(5))){
                abort(403, '验证码已失效');
            }

            $userCarte = $user->carte;
            $phone = '';
            if($userCarte && $userCarte->phone){
                $phone = $userCarte->phone;
            }else{
                $phone = $user->phone;
            }
            if(($verificationCodes->code !== $request->get('sms_code')) || ($verificationCodes->account !== $phone)){
                abort(403, '验证码不正确');
            }
        }

        DB::beginTransaction();
        try{
            $request->user()->cash_password = bcrypt($request->get('cash_password'));
            $request->user()->save();
            if($verificationCodes){
                $verificationCodes->state = VerificationCode::STATUS_USED;
                $verificationCodes->save();
            }
            DB::commit();
            return new UserResource(['code' => 200]);
        }catch (\Exception $exception) {
            DB::rollBack();
            abort(403, $exception->getMessage());
        }
    }
}
