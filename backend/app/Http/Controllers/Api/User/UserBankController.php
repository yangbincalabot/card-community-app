<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\UserBankRequest;
use App\Http\Resources\BankResource;
use App\Models\Bank;
use App\Services\UserBankService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\User\UserBank;
use App\Http\Resources\User\UserBankResource;

class UserBankController extends Controller
{

    public function index(Request $request, UserBankService $userBankService){
        $userBankInfo = $userBankService->getUserBanks($request->user()->id);
        return new UserBankResource($userBankInfo);
    }

    // 添加银行卡
    public function add(UserBankRequest $request, UserBankService $userBankService){
        try{
            DB::beginTransaction();
            $userBank = $userBankService->addUserBank($request);
            DB::commit();
            return new UserBankResource($userBank);
        }catch (\Exception $exception){
            DB::rollBack();
            abort(500, $exception->getMessage());
        }

    }

    // 银行卡信息
    public function detail(Request $request){
        $id = $request->get('id');
        if((!is_numeric($id)) || empty($id)){
            abort(404);
        }
        $userBank = UserBank::findOrFail($id);
        $this->authorize('own', $userBank);
        if($userBank->bank->is_use === Bank::IS_USER_FALSE){
            abort(404);
        }
        return new BankResource($userBank->load('bank'));
    }

    // 删除银行卡
    public function delete(Request $request){
        $userBank = UserBank::findOrFail($request->id);
        $this->authorize('own', $userBank);
        $userBank->delete();
    }

    // 编辑银行卡
    public function update(UserBankRequest $request, UserBankService $userBankService){
        $userBank = UserBank::findOrFail($request->get('id'));
        if(empty($userBank)){
            abort(404);
        }
        $this->authorize('own', $userBank);
        try{
            DB::beginTransaction();
            $userBank = $userBankService->updateUserBank($userBank, $request);
            DB::commit();
            return new UserBankResource($userBank);
        }catch ( \Exception $exception) {
            DB::rollBack();
            abort(500, $exception->getMessage());
        }
    }
}
