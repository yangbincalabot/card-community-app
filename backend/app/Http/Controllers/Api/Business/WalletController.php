<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Resources\CommonResource;
use App\Models\User\UserBalanceLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WalletController extends Controller
{
    public function index(Request $request){
        $user = $request->user();
        $money = UserBalanceLog::query()->where([
            'user_id' => $user->id,
            'type' => UserBalanceLog::TYPE_SALES_GOODS
        ])->sum('money');
        return new CommonResource(compact('money'));
    }

    public function detail(Request $request){
        $user = $request->user();
        $logs = UserBalanceLog::query()->where([
            'user_id' => $user->id,
            'type' => UserBalanceLog::TYPE_SALES_GOODS
        ])->with('user')->latest()->paginate();
        return new CommonResource(compact('logs'));
    }
}
