<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/27
 * Time: 14:34
 */

namespace App\Services;
use App\Models\Configure;
use App\Models\Store;
use App\Models\User;
use App\Events\UserMoneyEvent;
use App\Models\User\UserBalanceLog;
use App\Models\User\UserRelation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

// 推荐奖励
class RecommendRewardService
{
    protected $user;
    protected $money;
    protected $userApplyAgent;
    protected $parentUserInfo;
    protected $indirectParentUserInfo;

    /**
     * @param User $user 用户model
     * @param $userApplyAgent // 代理商信息
     * @param int $money // 代理商价格
     * @param int $recommend_level_one // 一级推荐奖励百分比
     * @param int $recommend_level_two // 二级推荐奖励百分比
     */
    public function computeReward原(User $user, $userApplyAgent, $money = 0, $recommend_level_one = 0, $recommend_level_two = 0){
        $this->user = $user;
        $this->money = $money;
        $this->userApplyAgent = $userApplyAgent;
        $recommend_level_one = bcdiv($recommend_level_one, 100, 10);
        $recommend_level_two = bcdiv($recommend_level_two, 100, 10);

        if($user){
            // 上级
            $user_parent_level_one = UserRelation::where('to_user_id', $user->id)->with('fromUser')->first();
            if($user_parent_level_one && $user_parent_level_one->fromUser){


                $this->handleParentMoney($user_parent_level_one->fromUser, $recommend_level_one, sprintf("%s  经您推荐成为%s", $user->nickname, $this->userApplyAgent->agent->name), $this->user->id);


                // 上上级
                $user_parent_level_two = UserRelation::where('to_user_id', $user_parent_level_one->from_user_id)->with('fromUser')->first();
                if($user_parent_level_two && $user_parent_level_two->fromUser){
                    $this->handleParentMoney($user_parent_level_two->fromUser, $recommend_level_two, sprintf("您的下级 %s 推荐 %s 成为%s", $user_parent_level_two->fromUser->nickname,
                        $user->nickname, $this->userApplyAgent->agent->name), $user_parent_level_one->from_user_id);
                }
            }



            // 若申请的是店中店才有额外奖励
            if($this->userApplyAgent->agent->type == User::USER_TYPE_TWO){
                $this->handleFacilitator();
            }

        }
    }


    // 处理推荐奖励
    protected  function handleParentMoney($parentUser, $rate, $remark = '', $from_user_id = 0){
        // 获取当前用户信息
        $userInfo = $this->user;

        $reward = bcmul($this->money, $rate, 10);
        $must_reward = round($reward, 2, PHP_ROUND_HALF_UP);
        if(bccomp($must_reward, 0.00, 2) === 1){
            $parentUser->balance->increment('money', $must_reward); // 更新用户可用余额
            $parentUser->balance->increment('reward_money', $must_reward); // 更新推荐代理奖励
            $parentUser->balance->increment('total_revenue', $must_reward); // 更新总收益


            // 修改用户密钥key
            event(new UserMoneyEvent($parentUser));

            $parentUser->balanceLogs()->create([
                'money' => $must_reward,
                'log_type' => UserBalanceLog::LOG_TYPE_INCOME,
                'type' => UserBalanceLog::TYPE_RECOMMEND,
                'remark' => $remark,
                'from_user_id' => $from_user_id
            ]);
        }
    }

    // 服务商额外奖励
    protected function handleFacilitator(){
        $configure = Configure::whereIn('name', ['ADMINISTRATIVE_DIVISION', 'EXTRA_RECOMMEND_REWARD'])->pluck('value', 'name')->toArray();
        $extra_recommend_reward = round($configure['EXTRA_RECOMMEND_REWARD'], 2, PHP_ROUND_HALF_UP);
        $condition = null;
        $agent_name = $this->userApplyAgent->agent->name;
        // 按市
        if(!$configure || $configure['ADMINISTRATIVE_DIVISION'] == Configure::ADMINISTRATIVE_DIVISION_CITY){
            $condition['city'] = $this->userApplyAgent->city;
        }else{
            // 按区县
            $condition['district'] = $this->userApplyAgent->district;
        }

        // 查找符合条件的服务商
        $facilitators = Store::where($condition)->whereHas('user', function($query){
            $query->where('type', User::USER_TYPE_FOUR)->with('balance');
        })->get();
        if($facilitators && bccomp($extra_recommend_reward, 0.00, 2) === 1){
            // 更新代理商可用金额，推荐代理奖励，总收益
            $userBalanceLogs = [];

            if($facilitators){
                foreach ($facilitators as $facilitator){
                    if($facilitator->user){
                        $facilitator->user->balance->increment('money', $extra_recommend_reward);
                        $facilitator->user->balance->increment('reward_money', $extra_recommend_reward);
                        $facilitator->user->balance->increment('total_revenue', $extra_recommend_reward);
                        event(new UserMoneyEvent($facilitator->user));

                        $userBalanceLogs[] = [
                            'user_id' => $facilitator->user->id,
                            'log_type' => UserBalanceLog::LOG_TYPE_INCOME,
                            'type' => UserBalanceLog::TYPE_EXTRA_RECOMMEND,
                            'money' => $extra_recommend_reward,
                            'remark' => sprintf("%s 加入%s，获得%.2f元额外奖励", $this->user->nickname,  $agent_name, $extra_recommend_reward),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'from_user_id' => $this->user->id
                        ];
                    }
                }
                UserBalanceLog::insert($userBalanceLogs);
            }

            
        }

    }

    /*=**********************************新版本分红规则************************************************=*/

    public function computeReward(User $user,$userApplyAgent){
        $this->user = $user;
        $this->userApplyAgent = $userApplyAgent;
        $money = $userApplyAgent->agent->price;
        $this->money = $money;
        $userParentInfo = UserRelation::where('to_user_id', $user->id)->with([
            'fromUser' => function($query){
                $query->with('balance');
            }
        ])->first();
        if($userParentInfo && $userParentInfo->fromUser){
            $this->parentUserInfo = $userParentInfo->fromUser;
            // 直接推荐人奖励
            $this->userParentReward($userApplyAgent->agent->type,$userParentInfo->fromUser, $userParentInfo->fromUser->type);

            // 间接推荐人奖励
            $userIndirectParentInfo = UserRelation::where('to_user_id', $userParentInfo->from_user_id)->with([
                'fromUser' => function($query){
                    $query->with('balance');
                }
            ])->first();
            if($userIndirectParentInfo && $userIndirectParentInfo->fromUser){
                $this->indirectParentUserInfo = $userIndirectParentInfo->fromUser;
                $this->userIndirectParentReward($userApplyAgent->agent->type,$userIndirectParentInfo->fromUser, $userIndirectParentInfo->fromUser->type);
            }
        }
    }


    // 直接推荐人奖励
    protected function userParentReward($userApplyType,$userParentInfo,$userParentType){
        Log::info('=====================userParentReward 开始==================');
        Log::info('$userApplyType='.$userApplyType);
        Log::info('直接推荐人信息=',$userParentInfo->toArray());


        $this->type = UserBalanceLog::TYPE_RECOMMEND;
        switch ($userApplyType){
            case User::USER_TYPE_ONE:
                $this->handleUserTypeOne($userParentInfo,$userParentType);
                break;
            case User::USER_TYPE_TWO:
                $this->handleUserTypeTwo($userParentInfo,$userParentType);
                break;
            case User::USER_TYPE_THREE:
                $this->handleUserTypeThree($userParentInfo,$userParentType);
                break;
            case User::USER_TYPE_FOUR:
                $this->handleUserTypeFour($userParentInfo,$userParentType);
                break;
        }
        Log::info('=====================userParentReward 结束==================');
    }

    // 当前用户类型，直接推荐人用户信息，直接推荐人用户类型
    protected function handleUserTypeOne($userParentInfo,$userParentType){
        Log::info('==================handleUserTypeOne 开始===========');
        Log::info('$userParentType='.$userParentType);
        $rewardMoney = 0;
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是普通会员，上级也是普通会员，则上级推荐奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是普通会员，上级是店中店，则上级推荐奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是普通会员，上级是总代理，则上级推荐奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是普通会员，上级是区域服务商，则上级推荐奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleUserTypeOne 结束===========');
    }

    protected function handleUserTypeTwo($userParentInfo,$userParentType){
        Log::info('==================handleUserTypeTwo 开始===========');
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是店中店，上级是普通会员，则上级奖励为 500
                $rewardMoney = 500;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是店中店，上级是店中店，则上级奖励为 20%
                $rewardMoney = bcdiv(bcmul($this->money,20,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是店中店，上级是总代理，则上级奖励为 20%
                $rewardMoney = bcdiv(bcmul($this->money,20,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是店中店，上级是区域服务商，则上级奖励为 20%
                $rewardMoney = bcdiv(bcmul($this->money,20,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleUserTypeTwo 结束===========');
    }

    protected function handleUserTypeThree($userParentInfo,$userParentType){
        Log::info('==================handleUserTypeThree 开始===========');
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是总代理，上级是普通会员，则上级奖励为 800
                $rewardMoney = 800;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是总代理，上级是店中店，则上级奖励为 20%
                $rewardMoney = bcdiv(bcmul($this->money,20,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是总代理，上级是总代理，则上级奖励为 20%
                $rewardMoney = bcdiv(bcmul($this->money,20,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是总代理，上级是区域服务商，则上级奖励为 20%
                $rewardMoney = bcdiv(bcmul($this->money,20,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleUserTypeThree 结束===========');
    }

    protected function handleUserTypeFour($userParentInfo,$userParentType){
        Log::info('==================handleUserTypeFour 开始===========');
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是区域服务商，上级是普通会员，则上级奖励为 100
                $rewardMoney = 1000;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是区域服务商，上级是店中店，则上级奖励为 20%
                $rewardMoney = bcdiv(bcmul($this->money,20,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是区域服务商，上级是总代理，则上级奖励为 20%
                $rewardMoney = bcdiv(bcmul($this->money,20,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是区域服务商，上级是区域服务商，则上级奖励为 20%
                $rewardMoney = bcdiv(bcmul($this->money,20,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleUserTypeFour 结束===========');
    }

    protected function handleUserParentReward($userParentInfo,$rewardMoney){
        Log::info('==================handleUserParentTypeOne 开始===========');
        $user = $this->user;
        $remark = sprintf("%s  经您推荐成为%s", $user->nickname, $this->userApplyAgent->agent->name);
        $this->handleParentRewardMoney($userParentInfo,$rewardMoney,$this->user->id,$remark);
        Log::info('==================handleUserParentTypeOne 结束===========');
    }


    // 间接推荐人奖励
    protected function userIndirectParentReward($userType,$userParentInfo,$userParentType){
        Log::info('间接推荐人信息=',$userParentInfo->toArray());
        $this->type = UserBalanceLog::TYPE_SALES;
        switch ($userType){
            case User::USER_TYPE_ONE:
                $this->handleIndirectParentUserTypeOne($userParentInfo,$userParentType);
                break;
            case User::USER_TYPE_TWO:
                $this->handleIndirectParentUserTypeTwo($userParentInfo,$userParentType);
                break;
            case User::USER_TYPE_THREE:
                $this->handleIndirectParentUserTypeThree($userParentInfo,$userParentType);
                break;
            case User::USER_TYPE_FOUR:
                $this->handleIndirectParentUserTypeFour($userParentInfo,$userParentType);
                break;
        }
    }

    // 当前用户类型，间接推荐人用户信息，间接推荐人用户类型
    protected function handleIndirectParentUserTypeOne($userParentInfo,$userParentType){
        Log::info('==================handleIndirectParentUserTypeOne 开始===========');
        Log::info('$userParentType='.$userParentType);
        $rewardMoney = 0;
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是普通会员，间接上级也是普通会员，则间接上级奖励为 0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是普通会员，间接上级是店中店，则间接上级奖励为 0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是普通会员，间接上级是总代理，则间接上级奖励为 0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是普通会员，间接上级是区域服务商，则间接上级奖励为 0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleIndirectParentUserTypeOne 结束===========');
    }

    protected function handleIndirectParentUserTypeTwo($userParentInfo,$userParentType){
        Log::info('==================handleIndirectParentUserTypeTwo 开始===========');
        Log::info('$userParentType='.$userParentType);
        $rewardMoney = 0;
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是店中店，间接上级是普通会员，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是店中店，间接上级是店中店，则间接上级奖励为 10%
                $rewardMoney = bcdiv(bcmul($this->money,10,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是店中店，间接上级是总代理，则间接上级奖励为 10%
                $rewardMoney = bcdiv(bcmul($this->money,10,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是店中店，间接上级是区域服务商，则间接上级奖励为 10%
                $rewardMoney = bcdiv(bcmul($this->money,10,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleIndirectParentUserTypeTwo 结束===========');
    }

    protected function handleIndirectParentUserTypeThree($userParentInfo,$userParentType){
        Log::info('==================handleIndirectParentUserTypeThree 开始===========');
        Log::info('$userParentType='.$userParentType);
        $rewardMoney = 0;
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是总代理，间接上级是普通会员，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是总代理，间接上级是店中店，则间接上级奖励为 10%
                $rewardMoney = bcdiv(bcmul($this->money,10,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是总代理，间接上级是总代理，则间接上级奖励为 10%
                $rewardMoney = bcdiv(bcmul($this->money,10,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是总代理，间接上级是区域服务商，则间接上级奖励为 10%
                $rewardMoney = bcdiv(bcmul($this->money,10,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleIndirectParentUserTypeThree 结束===========');
    }

    protected function handleIndirectParentUserTypeFour($userParentInfo,$userParentType){
        Log::info('==================handleIndirectParentUserTypeFour 开始===========');
        Log::info('$userParentType='.$userParentType);
        $rewardMoney = 0;
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是区域服务商，间接上级是普通会员，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是区域服务商，间接上级是店中店，则间接上级奖励为 10%
                $rewardMoney = bcdiv(bcmul($this->money,10,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是区域服务商，间接上级是总代理，则间接上级奖励为 10%
                $rewardMoney = bcdiv(bcmul($this->money,10,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是区域服务商，间接上级是区域服务商，则间接上级奖励为 10%
                $rewardMoney = bcdiv(bcmul($this->money,10,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleIndirectParentUserTypeFour 结束===========');
    }

    protected function handleUserIndirectParentReward($userParentInfo,$rewardMoney){
        Log::info('==================handleUserIndirectParentReward 开始===========');
        $user = $this->user;
        $remark = sprintf("您的下级 %s 推荐 %s 成为%s", $this->parentUserInfo->nickname, $user->nickname, $this->userApplyAgent->agent->name);
        $this->handleParentRewardMoney($userParentInfo,$rewardMoney,$this->user->id,$remark);
        Log::info('==================handleUserIndirectParentReward 结束===========');
    }


    // 处理推荐奖励
    protected  function handleParentRewardMoney($parentUser, $reward, $from_user_id = 0, $remark = ''){

        $must_reward = round($reward, 2, PHP_ROUND_HALF_UP);
        if(bccomp($must_reward, 0.00, 2) === 1){
            $parentUser->balance->increment('money', $must_reward); // 更新用户可用余额
            $parentUser->balance->increment('reward_money', $must_reward); // 更新推荐代理奖励
            $parentUser->balance->increment('total_revenue', $must_reward); // 更新总收益

            // 修改用户密钥key
            event(new UserMoneyEvent($parentUser));
            Log::info('分红金额：'.$must_reward);
            Log::info('分红日志：'.$remark);
            Log::info('分红from_user_id：'.$from_user_id);

            $parentUser->balanceLogs()->create([
                'money' => $must_reward,
                'log_type' => UserBalanceLog::LOG_TYPE_INCOME,
                'type' => UserBalanceLog::TYPE_RECOMMEND,
                'remark' => $remark,
                'from_user_id' => $from_user_id
            ]);
        }else{
            Log::info('分红金额小于等于0，本次分红结束');
        }
    }
}