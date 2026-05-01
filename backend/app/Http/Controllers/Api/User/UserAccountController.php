<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\UserAccountResource;
use App\Models\User\UserBalanceLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserAccountController extends Controller
{

    // 账户概览
    public function detail(Request $request){
        return new UserAccountResource($request->user()->balance);
    }

    public function logs(Request $request){
        $appends = [];
        $log_type = $request->get('log_type');
        $type = $request->get('type');
        $query = UserBalanceLog::query()->where('user_id', $request->user()->id);
        if($log_type){
            $query->where('log_type', $log_type);
            $appends['log_type'] = $log_type;
        }
        if($type){
            $query->where('type', $type);
            $appends['type'] = $type;
        }

        $account_logs = $query->latest()->paginate()->appends($appends);
        foreach ($account_logs as $account_log){
            $account_log->type_text = UserBalanceLog::getTypeText($account_log->type);
        }
        return new UserAccountResource($account_logs);
    }
}
