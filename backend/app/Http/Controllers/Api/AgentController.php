<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AgentResource;
use App\Models\Agent;
use App\Models\Configure;
use App\Models\User;
use App\Models\User\UserApplyAgent;
use App\Models\User\UserBalanceLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User\UserRelation;

class AgentController extends Controller
{
    protected $user;

    // 代理中心
    public function getAgentInfo(Request $request){
        $userInfo = $request->user()->load(['agent', 'balance','store']);
        if($request->user()->agent_id > 0){
            $userInfo->todayIncome = $request->user()->balanceLogs()->whereBetween('created_at', [Carbon::today(), Carbon::tomorrow()->addSeconds(-1)])
                ->where('log_type', UserBalanceLog::LOG_TYPE_INCOME)->whereIn('type',UserBalanceLog::getTypeReward())->sum('money');
        }
        return new AgentResource($userInfo);
    }

    // 我的下级代理
    public function getMyLowers(Request $request){
        $this->user = $request->user();
        $lowers = UserRelation::query()->where('from_user_id', $this->user->id)->whereHas('toUser', function($query){
            $query->where('agent_id', '>', 0);
        })->with(['toUser' => function($query){
                $query->with(['lowerBalanceLogs' => function($query){
                    $query->where('user_id', $this->user->id)->whereIn('type', UserBalanceLog::getTypeReward())->where('log_type', UserBalanceLog::LOG_TYPE_INCOME);
                }])->orderBy('agent_time', 'desc');
        }])->paginate();

        foreach ($lowers as $lower){
            $lower->toUser->total_revenue = sprintf("%.2f", $lower->toUser->lowerBalanceLogs ? $lower->toUser->lowerBalanceLogs->sum('money') : 0);
        }

        return new AgentResource($lowers);
    }

    // 代理详细
    public function getLowerDetail(Request $request){
        $this->user = $request->user();
        $lowerInfo = UserRelation::query()->where(['id' => $request->get('id'), 'from_user_id' => $this->user->id])->with(['toUser' => function($query){
            $query->with(['lowerBalanceLogs' => function($query){
                $query->where('user_id', $this->user->id)->whereIn('type', UserBalanceLog::getTypeReward())->where('log_type', UserBalanceLog::LOG_TYPE_INCOME);
            }, 'applyAgents' => function($query){
                $query->where('status', UserApplyAgent::APPLY_STATUS_SUCCESS);
            }, 'agent']);
        }])->first();
        $lowerInfo->toUser->total_revenue = sprintf("%.2f", $lowerInfo->toUser->lowerBalanceLogs ? $lowerInfo->toUser->lowerBalanceLogs->sum('money') : 0);
        if(!$lowerInfo){
            abort(404);
        }
        return new AgentResource($lowerInfo);
    }

    public function getLowerDetailLog(Request $request){
        $relations = UserRelation::find($request->get('id'));
        $lowerLogs = UserBalanceLog::query()->where([
            'user_id' => $request->user()->id,
            'from_user_id' => $relations->to_user_id
        ])->paginate()->appends(['id' => $request->get('id')]);
        return new AgentResource($lowerLogs);
    }


    // 成为代理页面的选项
    public function getAgents(){
        $agents = Agent::orderBy('sort', 'DESC')
            ->orderBy('id', 'DESC')->get();

        // 联系号码
        $configure = Configure::where('name', 'CONTACT_NUMBER')->first();
        $contact_number = $configure ? $configure->value : '';


        return new AgentResource(compact('agents', 'contact_number'));
    }
}
