<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\WithdrawResource;
use App\Models\Bank;
use App\Models\User\UserBalance;
use App\Models\User\UserBank;
use App\Services\WithdrawService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class WithdrawController extends Controller
{

    public function index(Request $request, WithdrawService $withdrawService){
        $withdraws = $withdrawService->index($request->user());
        return new WithdrawResource($withdraws);
    }

    // 提现
    public function add(WithdrawRequest $request, WithdrawService $withdrawService){
        $user = $request->user();
        $userBalance = $user->balance;
        // 虽然新用户有默认会新建记录，但防止数据删除，这里需要判断
        if(empty($userBalance)){
            $userBalance = UserBalance::addDefaultData($user->id);
        }

        if($userBalance->money < $request->get('money')){
            abort(403, '提现金额不能大于可用金额');
        }

        // 验证支付密码
        if(!Hash::check($request->get('cash_password'), $user->cash_password)){
            abort(403, '支付密码错误');
        }

        // 验证银行卡状态
        $userBank = UserBank::find($request->get('user_bank_id'));
        $bank = $userBank->bank;
        if(empty($bank) || $bank->is_use === Bank::IS_USER_FALSE || !empty($bank->deleted_at)){
            abort(403, '非法操作');
        }
        return new WithdrawResource($withdrawService->add($user, $request->all()));
    }
}
