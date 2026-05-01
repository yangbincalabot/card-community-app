<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/21
 * Time: 9:52
 */

namespace App\Services;
use App\Events\UserMoneyEvent;
use App\Models\User\UserBalance;
use App\Models\User\UserBalanceLog;
use App\Models\Withdraw;
use DB;
use Log;

class WithdrawService
{


    public function index($user){
        $data = [];
        $data['withdraws'] = Withdraw::where('user_id', $user->id)
            ->orderBy('id', 'desc')->with('bank')->paginate();

        // 累计提现金额
        $data['total_withdraw'] = $user->withdraw()->where('status', Withdraw::WITHDRAW_STATUS_SUCCESS)->sum('money');
        return $data;
    }

     // 申请提现
    public function add($user, $formData){
        try{
            DB::beginTransaction();
            $money = moneyShow($formData['money']);
            // 添加数据到提现表
            $user->withdraw()->create([
                'user_bank_id' => $formData['user_bank_id'],
                'status' => Withdraw::WITHDRAW_STATUS_STAY,
                'money' => $money
            ]);

            // 减去用户可用金额
            $user->balance->decrement('money', $formData['money']);

            // 增加用户冻结金额
            $user->balance->increment('frozen_money', $formData['money']);

            // 用户流水记录
            UserBalanceLog::addLog($user->id, UserBalanceLog::LOG_TYPE_PAY, UserBalanceLog::TYPE_WITHDRAW, $money);


            // 修改密钥key
            event(new UserMoneyEvent($user));
            DB::commit();
        }catch (\Exception $exception){
            Log::error($exception->getMessage());
            DB::rollBack();
        }
        return $user;
    }
}