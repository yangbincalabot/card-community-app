<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BankResource;

use App\Models\Bank;

use App\Http\Controllers\Controller;

class BankController extends Controller
{

    // 获取银行卡列表
    public function index(){
        $banks = Bank::isUse()->orderBy('id', 'desc')->get(['id', 'name']);
        return new BankResource($banks);
    }
}
