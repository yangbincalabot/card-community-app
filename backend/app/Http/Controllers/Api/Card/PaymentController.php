<?php

namespace App\Http\Controllers\Api\Card;

use App\Events\UserMoneyEvent;
use App\Http\Requests\CompanyCardPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Association;
use App\Models\CompanyCard;
use App\Models\CompanyCardLog;
use App\Models\CompanyCardRole;
use App\Models\CompanyRole;
use App\Models\Configure;
use App\Models\PlatformIncome;
use App\Models\User;
use App\Models\User\UserBalance;
use App\Models\User\UserBalanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\User\UserAuth;
use Illuminate\Support\Facades\Hash;

class PaymentController extends Controller
{
    // 验证开通企业会员费用，为0不走支付接口
    // 开通企业会员支付(微信支付)
    public function createCompanyCardCharge(CompanyCardPaymentRequest $request){
        $businessCost = Configure::getValue('BUSINESS_COST');
        $order_no = createOrderNo();
        $user = $request->user();
        // 走支付流程,免费不添加平台收益
        if(bccomp($businessCost, 0.00, 2) === 1){
            DB::beginTransaction();
            try{
                $this->userBalanceLogs($user, $businessCost);
                $this->companyCardLogs($user, $businessCost, $order_no,CompanyCardLog::PAY_WECHAT);
                // 统一下单
                $userAuthWhere['user_id'] = $user->id;
                $userAuthWhere['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
                $userOpenId = UserAuth::where($userAuthWhere)->value('identifier');
                $money = bcmul($businessCost,100);
                $order = [
                    'out_trade_no' => $order_no,
                    'body' => '开通企业会员',
                    'total_fee' => $money,
                    'openid' => $userOpenId,
                    'notify_url' => route('company_card.wechat.notify'),
                ];
                $pay = app('wechat_pay')->miniapp($order);
                DB::commit();
                return new PaymentResource($pay);
            }catch (\Exception $e){
                \Log::error($e->getMessage());
                DB::rollback();
                abort(500, '生成支付出错');
            }

        }else{
            // 直接修改会员状态
            $this->updateUserType($user);
            $companyCarLog = $this->companyCardLogs($user, $businessCost, $order_no,CompanyCardLog::PAY_WECHAT);
            $companyCarLog->is_pay = CompanyCardLog::PAY_PAID;
            $companyCarLog->paid_at = Carbon::now();
            $companyCarLog->save();
            return new PaymentResource($user);
        }


    }


    // 余额支付
    public function createCompanyCardBalance(CompanyCardPaymentRequest $request){
        $businessCost = Configure::getValue('BUSINESS_COST');
        $user = $request->user();
        if(bccomp($businessCost, 0.00, 2) >= 0){
            DB::beginTransaction();
            try{
                $userBalancce = $user->balance;
                if(!$userBalancce){
                    UserBalance::addDefaultData($user->id);
                }
                if(bccomp($userBalancce->money, $businessCost, 2) === -1){
                    abort(403, '余额不足');
                }
                if(bccomp($businessCost, 0.00, 2) === 1){
                    $userBalancce->decrement('money', $businessCost);
                    // 修改秘钥
                    event(new UserMoneyEvent($user));
                }
                $order_no = createOrderNo();
                $this->userBalanceLogs($user, $businessCost);
                $companyCarLog = $this->companyCardLogs($user, $businessCost, $order_no, CompanyCardLog::PAY_BALANCE);
                $companyCarLog->is_pay = CompanyCardLog::PAY_PAID;
                $companyCarLog->save();
                // 修改会员状态
                $this->updateUserType($user);

                // 平台收益
                $this->addPlatformIncome($user, $businessCost);
                DB::commit();
                return new PaymentResource($user);
            }catch (\Exception $e) {
                DB:: rollBack();
                \Log::error($e->getMessage());
                abort(500, '系统出错');
            }

        }else{
            abort(403, '非法操作');
        }
    }


    // 会员流水
    protected function userBalanceLogs(User $user, $money){
        return UserBalanceLog::addLog($user->id, UserBalanceLog::LOG_TYPE_PAY, UserBalanceLog::TYPE_COMPANY_FEE, $money);
    }

    // 开通记录
    protected function companyCardLogs(User $user, $money,$order_no, $pay_type){
        return CompanyCardLog::addLogs($user->id, $money, $order_no, $pay_type);
    }

    // 修改会员状态
    protected function updateUserType(User $user){
        $user->type = User::USER_TYPE_TWO; // 企业会员状态
        // 累加1年
        if($user->enterprise_at && $user->enterprise_at->gt(Carbon::now())){
            $user->enterprise_at = $user->enterprise_at->addYears(1);
        }else{
            $user->enterprise_at = Carbon::now()->addYears(1);
        }

        // 修改企业状态为正常
        $companyCard = $user->companyCard;
        if(!$companyCard){
            $companyCard = CompanyCard::addDefaultCompanyCard($user->id, '', $user->avatar);
        }
        if($companyCard->status !== CompanyCard::TYPE_DELETE){
            $companyCard->status = CompanyCard::TYPE_NORMAL;
            $companyCard->save();
        }

         // 修改名片的绑定
        $carte = $user->carte;
        if($carte){
            $carte->cid = $companyCard->id;
            $carte->save();
        }

        // 开通企业会员会加入到默认协会中
        $association = Association::query()->where('user_id', 0)->first();
        if ($association){
            // 获取角色，目前获取最后的角色id
            $role_id = CompanyRole::query()->where(['aid' => $association->id])->orderBy('sort', 'desc')->value('id');
            if ($role_id > 0){
                $maxSort = CompanyCardRole::query()->where(['aid' => $association->id, 'role_id' => $role_id])->max('role_sort') + 1;
                CompanyCardRole::query()->firstOrCreate(['company_id' => $companyCard->id, 'role_id' => $role_id, 'aid' => $association->id], ['role_sort' => $maxSort]);
            }
        }

        $user->save();
    }

    // 微信异步回调
    public function wechatPayNotify(Request $request){
        DB::beginTransaction();
        try{
            // 校验回调参数是否正确
             $data = app('wechat_pay')->verify();


            $companyCardLog = CompanyCardLog::query()->where('order_no', $data->out_trade_no)->first();
            if(!$companyCardLog){
                return 'fail';
            }

            // 检查是否已支付
            if($companyCardLog->is_pay === CompanyCardLog::PAY_PAID){
                return app('wechat_pay')->success();
            }

            // 修改记录状态
            $companyCardLog->payment_no = $data->transaction_id;
            $companyCardLog->paid_at = Carbon::now();
            $companyCardLog->is_pay = CompanyCardLog::PAY_PAID;
            $companyCardLog->save();

            // 修改会员类型
            $user = User::find($companyCardLog->user_id);
            if($user){
                // 修改会员状态
                $this->updateUserType($user);

                // 添加平台收益
                $this->addPlatformIncome($user, $companyCardLog->money);
            }
            DB::commit();
            return app('wechat_pay')->success();
        }catch (\Exception $e){
            DB::rollBack();
            \Log::error($e->getMessage());
            return 'fail';
        }
    }

    // 添加平台收益
    protected function addPlatformIncome(User $user, $money){
        return PlatformIncome::addPlatformIncome($user->id, PlatformIncome::COMPANY_TYPE, $money, 0, sprintf('微信昵称 %s 升级企业会员', $user->nickname));
    }
}
