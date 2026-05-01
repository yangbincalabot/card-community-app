<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/17
 * Time: 16:38
 */

namespace App\Services;


use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\User;
use App\Models\User\UserBank;

class UserBankService
{
    public function getUserBanks($user_id){
        $userBankInfo = User::where('id', $user_id)->with(['userBanks' => function($query){
            $query->with(['bank' => function($query){
                $query->where('is_use', Bank::IS_USER_TRUE);
            }])->orderBy('id', 'desc');
        }])->first();

        $userBanks = $userBankInfo->userBanks->filter(function ($userBank){
            return boolval($userBank->bank);
        });
        return $userBanks;
    }

    public function addUserBank(Request $request){
        return UserBank::create([
            'bank_id' => $request->get('bank_id'),
            'card_name' => $request->get('card_name'),
            'card_number' => $request->get('card_number'),
            'user_id' => $request->user()->id,
        ]);
    }

    public function updateUserBank(UserBank $userBank, Request $request){
        $userBank->bank_id = $request->get('bank_id');
        $userBank->card_name = $request->get('card_name');
        $userBank->card_number = $request->get('card_number');
        $userBank->save();
        return $userBank;
    }
}