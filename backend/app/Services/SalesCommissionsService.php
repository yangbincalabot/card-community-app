<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/27
 * Time: 20:06
 */
namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RewardNote;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Configure;
use App\Models\User\UserRelation;
use App\Events\UserMoneyEvent;
use App\Models\User\UserBalanceLog;
use DB;
use App\Models\Store;
use Faker\ORM\Spot\ColumnTypeGuesser;
use Illuminate\Support\Facades\Log;

// 销售提成
class SalesCommissionsService{
    protected $user;
    protected $order_money;
    protected $order_id;
    protected $log_type; // 操作日志的类型
    protected $type; // 金额类型
    protected $order_info; // 金额类型

    // 流程： 冻结->入账或取消


    // 冻结
    public function freeze(User $user, $order_money, $order_id){
        $this->log_type = UserBalanceLog::LOG_TYPE_FREEZE;
        /*$this->user = $user;
        $this->order_money = $order_money;
        $this->order_id = $order_id;
        $this->computeSale();*/
        $this->reward($user, $order_money, $order_id);
    }



    // 入账
    public function enterAccount(User $user, $order_money, $order_id){
        $this->log_type = UserBalanceLog::LOG_TYPE_INCOME;
        /*$this->user = $user;
        $this->order_id = $order_id;
        $this->order_money = $order_money;
        $this->computeSale();*/
        $this->reward($user, $order_money, $order_id);
    }



    // 取消
    public function cancel(User $user, $order_money, $order_id){
        $this->log_type = UserBalanceLog::LOG_TYPE_CANCEL;
        /*$this->user = $user;
        $this->order_id = $order_id;
        $this->order_money = $order_money;
        $this->computeSale();*/
        $this->reward($user, $order_money, $order_id);
    }


    /**
     * 计算销售提成
     * @param User $user '用户模型'
     * @param $order_money '订单金额'
     */
    protected function computeSale(){
        DB::transaction(function (){
            // 只有普通会员或一级代理才计算提成
            if($this->user->type === User::USER_TYPE_ONE){
                $this->handleCommonUser();
            }elseif ($this->user->type === User::USER_TYPE_TWO){
                $this->handleAgent();
            }
        });

    }

    public function reward(User $user, $order_money, $order_id){
        Log::info('==========reward 开始===============');
        $this->user = $user;
        $this->order_id = $order_id;
        $this->order_money = $order_money;
        $orderInfo = $this->getOrderProductByOrderId($order_id);
        $this->order_info = $orderInfo;
        Log::info('==========$this->handle(); 开始===============');
        $this->handle();
        Log::info('==========$this->handle(); 结束===============');
        Log::info('==========reward 结束===============');
    }

    protected function handle(){
        $user_type = $this->user->type;
        $user_id = $this->user->id;
        $user_parent = UserRelation::where('to_user_id', $user_id)->with([
            'fromUser' => function($query){
                $query->with('balance');
            }
        ])->first();

        $where['user_type'] = $user_type;
        // 直接推荐人奖励
        if(!empty($user_parent) && isset($user_parent->fromUser)){
            $where['parent_type'] = $user_parent->fromUser->type;
            // 如果间接推荐人存在
            if(!empty($user_indirect_parent) && isset($user_parent->fromUser)){
                $where['indirect_type'] = $user_indirect_parent->fromUser->type;
            }
            $user_indirect_parent = UserRelation::where('to_user_id', $user_parent->from_user_id)->with([
                'fromUser' => function($query){
                    $query->with('balance');
                }
            ])->first();
            // 直接推荐人奖励
            $this->userParentReward($user_type,$user_parent->fromUser,$user_parent->fromUser->type);
            Log::info('==========$this->userParentReward 结束===============');
            // 间接推荐人奖励
            if(!empty($user_indirect_parent) && isset($user_parent->fromUser)){
                Log::info('==========间接推荐人奖励 userIndirectParentReward 开始===============');
                $this->userIndirectParentReward($user_type,$user_indirect_parent->fromUser,$user_indirect_parent->fromUser->type);
                Log::info('==========间接推荐人奖励 userIndirectParentReward 结束===============');
            }
        }
        // 区域服务商奖励
        Log::info('==========区域服务商奖励 handleServiceProvider 开始===============');
        $this->handleServiceProvider();
        Log::info('==========区域服务商奖励 handleServiceProvider 结束===============');
    }


    // 直接推荐人奖励
    protected function userParentReward($userType,$userParentInfo,$userParentType){
        Log::info('=====================userParentReward 开始==================');
        Log::info('$userType='.$userType);
        Log::info('直接推荐人信息=',$userParentInfo->toArray());
        $this->type = UserBalanceLog::TYPE_SALES;
        switch ($userType){
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

    // 当前用户类型，直接推荐人用户类型
    protected function handleUserTypeOne($userParentInfo,$userParentType){
        Log::info('==================handleUserTypeOne 开始===========');
        Log::info('$userParentType='.$userParentType);
        $rewardMoney = 0;
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是普通会员，上级也是普通会员，则上级奖励为订单的 25%
                $rewardMoney = bcdiv(bcmul($this->order_money,25,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是普通会员，上级是店中店，则上级奖励为零售价-店中店拿货价
                $rewardMoney = $this->getUserTypePriceDifference('yjdl_price');
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是普通会员，上级是总代理，则上级奖励为 30%
                $rewardMoney = bcdiv(bcmul($this->order_money,30,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是普通会员，上级是区域服务商，则上级奖励为零售价-服务商拿货价
                $rewardMoney = $this->getUserTypePriceDifference('qyfws_price');
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleUserTypeOne 结束===========');
    }

    protected function handleUserTypeTwo($userParentInfo,$userParentType){
        Log::info('==================handleUserTypeTwo 开始===========');
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是店中店，上级是普通会员，则上级奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是店中店，上级是店中店，则上级奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是店中店，上级是总代理，则上级奖励为 6%
                $rewardMoney = bcdiv(bcmul($this->order_money,6,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是店中店，上级是区域服务商
                // 店中店须和服务商在同一城市，则上级奖励为店中店拿货价8%（个人理解为订单金额的 8%）
                // 否则推荐人没有收益
                // ----------获取当前会员店中店所在地区-------
                $userStoreAddress = Store::where('user_id',$this->user->id)->first();
                // ----------获取当前会员直接推荐人所在地区-------
                $userParentStoreAddress = Store::where('user_id',$userParentInfo->id)->first();
                // 获取地区验证级别，验证到市，还是验证到区县
                $configure = Configure::whereIn('name', ['COMMON_SALES_REWARD', 'ADMINISTRATIVE_DIVISION', 'SALES_FACILITATOR_LEVEL'])->pluck('value', 'name')->toArray();
                // 如果在同一地区，则有奖励，否则奖励为 0
                $rewardMoney = 0;
                // 按市
                if(isset($configure['ADMINISTRATIVE_DIVISION']) && $configure['ADMINISTRATIVE_DIVISION'] == Configure::ADMINISTRATIVE_DIVISION_CITY){
                    if($userStoreAddress->city == $userParentStoreAddress->city){
                        $rewardMoney = bcdiv(bcmul($this->order_money,8,10),100,2);
                    }
                }else{
                    // 按区县
                    if($userStoreAddress->district == $userParentStoreAddress->district){
                        $rewardMoney = bcdiv(bcmul($this->order_money,8,10),100,2);
                    }
                }
                if($rewardMoney == 0){
                    Log::info('店中店和他的服务商直接推荐人不在同一城市，无法获得分红');
                }
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleUserTypeTwo 结束===========');
    }

    protected function handleUserTypeThree($userParentInfo,$userParentType){
        Log::info('==================handleUserTypeThree 开始===========');
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是总代理，上级是普通会员，则上级奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是总代理，上级是店中店，则上级奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是总代理，上级是总代理，则上级奖励为 6%
                $rewardMoney = bcdiv(bcmul($this->order_money,6,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是总代理，上级是区域服务商，则上级奖励为店中店拿货价6%（个人理解为订单金额的 6%）
                $rewardMoney = bcdiv(bcmul($this->order_money,6,10),100,2);
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleUserTypeThree 结束===========');
    }

    protected function handleUserTypeFour($userParentInfo,$userParentType){
        Log::info('==================handleUserTypeFour 开始===========');
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是区域服务商，上级是普通会员，则上级奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是区域服务商，上级是店中店，则上级奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是区域服务商，上级是总代理，则上级奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是区域服务商，上级是区域服务商，则上级奖励为0
                $rewardMoney = 0;
                $this->handleUserParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleUserTypeFour 结束===========');
    }

    protected function handleUserParentReward($userParentInfo,$rewardMoney){
        Log::info('==================handleUserParentTypeOne 开始===========');
        $this->handleParentRewardMoney($userParentInfo,$rewardMoney,$this->user->id);
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

    // 当前用户类型，间接推荐人用户信息,间接推荐人用户类型
    protected function handleIndirectParentUserTypeOne($userParentInfo,$userParentType){
        Log::info('==================handleIndirectParentUserTypeOne 开始===========');
        Log::info('$userParentType='.$userParentType);
        $rewardMoney = 0;
        switch ($userParentType){
            case User::USER_TYPE_ONE:
                // 如果会员是普通会员，间接上级也是普通会员，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是普通会员，间接上级是店中店，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是普通会员，间接上级是总代理，则间接上级奖励为 5%
                $rewardMoney = bcdiv(bcmul($this->order_money,5,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是普通会员，间接上级是区域服务商，则间接上级奖励为0
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
                // 如果会员是店中店，间接上级也是普通会员，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是店中店，间接上级是店中店，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是店中店，间接上级是总代理，则间接上级奖励为 3%
                $rewardMoney = bcdiv(bcmul($this->order_money,3,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是店中店，间接上级是区域服务商，则间接上级奖励为0
                $rewardMoney = 0;
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
                // 如果会员是总代理，间接上级也是普通会员，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是总代理，间接上级是店中店，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是总代理，间接上级是总代理，则间接上级奖励为 3%
                $rewardMoney = bcdiv(bcmul($this->order_money,3,10),100,2);
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是总代理，间接上级是区域服务商，则间接上级奖励为0
                $rewardMoney = 0;
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
                // 如果会员是区域服务商，间接上级也是普通会员，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_TWO:
                // 如果会员是区域服务商，间接上级是店中店，则间接上级奖励为 0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_THREE:
                // 如果会员是区域服务商，间接上级是总代理，则间接上级奖励为 0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
            case User::USER_TYPE_FOUR:
                // 如果会员是区域服务商，间接上级是区域服务商，则间接上级奖励为0
                $rewardMoney = 0;
                $this->handleUserIndirectParentReward($userParentInfo,$rewardMoney);
                break;
        }
        Log::info('==================handleIndirectParentUserTypeFour 结束===========');
    }

    protected function handleUserIndirectParentReward($userParentInfo,$rewardMoney){
        Log::info('==================handleUserIndirectParentReward 开始===========');
        $this->handleParentRewardMoney($userParentInfo,$rewardMoney,$this->user->id);
        Log::info('==================handleUserIndirectParentReward 结束===========');
    }


    // 收货地址所在服务商分红
    protected function handleServiceProvider(){
        $configure = Configure::whereIn('name', ['COMMON_SALES_REWARD', 'ADMINISTRATIVE_DIVISION', 'SALES_FACILITATOR_LEVEL'])->pluck('value', 'name')->toArray();
        // 订单地址
        $order = Order::find($this->order_id);
        if($order){
            $address = $order->address;
            if(isset($address['id']) && !empty($address['id'])){
                $user_address = UserAddress::find($address['id']);
                if($user_address){
                    $condition = [];
                    // 按市
                    if(isset($configure['ADMINISTRATIVE_DIVISION']) && $configure['ADMINISTRATIVE_DIVISION'] == Configure::ADMINISTRATIVE_DIVISION_CITY){
                        $condition['city'] = $user_address->city;
                    }else{
                        // 按区县
                        $condition['district'] = $user_address->district;
                    }
                    $facilitators = Store::where($condition)->whereHas('user', function($query){
                        $query->where('type', User::USER_TYPE_FOUR);
                    })->with([
                        'user' => function($query){
                            $query->with('balance');
                        }
                    ])->get();
                    $facilitators_count = $facilitators->count();
                    if($facilitators_count > 0){
                        $percent = $configure['SALES_FACILITATOR_LEVEL']; // 服务商分红比例
                        $serviceAmount = bcdiv(bcmul($this->order_money,$percent,10),100,10); // 服务商分红总金额
                        // 服务商销售提成平分
                        $everyoneServiceAmount = bcdiv($serviceAmount, $facilitators_count, 10);
                        Log::info('服务商销售提成平分金额='.$everyoneServiceAmount);
                        $this->type = UserBalanceLog::TYPE_SALES_SERVICE;
                        foreach ($facilitators as $facilitator){
                            if($facilitator->user){
                                $this->handleParentRewardMoney($facilitator->user, $everyoneServiceAmount, $this->user->id);
                            }
                        }
                    }
                }
            }
        }
    }

    protected  function handleParentRewardMoney($user, $rewardMoney, $from_user_id = 0){
        if(bccomp($rewardMoney, 0.01, 10) === -1){
            Log::info('分红金额='.$rewardMoney.'小于0.01,不满足分红条件');
            return false;
        }
        Log::info('$this->log_type='.$this->log_type);
        switch ($this->log_type){
            // 冻结
            case UserBalanceLog::LOG_TYPE_FREEZE:
                if(bccomp($rewardMoney, 0.01, 10) === -1){
                    return false;
                }

                $must_reward = round($rewardMoney, 2, PHP_ROUND_HALF_UP);
                // 增加销售冻结金额
                $user->balance->increment('sales_freeze_money', $must_reward);
                Log::info('sales_freeze_money加'.$must_reward);
                if(bccomp($must_reward, 0.00, 2) === 1){
                    $createData['money'] = $must_reward;
                    $createData['log_type'] = $this->log_type;
                    $createData['type'] = $this->type;
                    $createData['remark'] = sprintf("%s 拿货%.2f元", $this->user->nickname, $this->order_money);
                    $createData['from_user_id'] = $from_user_id;
                    $createData['order_id'] = $this->order_id;
                    Log::info('$createData',$createData);
                    $user->balanceLogs()->create($createData);
                }
                break;
            // 入账
            case UserBalanceLog::LOG_TYPE_INCOME:
                $user_balance_log = UserBalanceLog::where('user_id', $user->id)->where('type',$this->type)->where('order_id', $this->order_id)->get();
                if($user_balance_log){
                    $user->balance->increment('money', $user_balance_log->sum('money')); // 更新用户可用余额
                    $user->balance->increment('sales_money', $user_balance_log->sum('money')); // 更新销售提成奖励
                    $user->balance->increment('total_revenue', $user_balance_log->sum('money')); // 更新总收益
                    $user->balance->decrement('sales_freeze_money', $user_balance_log->sum('money')); // 减去销售冻结金额
                    // 修改用户密钥key
                    event(new UserMoneyEvent($user));
                    // 修改日记状态
                    $user_balance_log->log_type = $this->log_type;
                    $userBalanceLogUpdateData['log_type'] = $this->log_type;
                    UserBalanceLog::where('user_id', $user->id)->where('order_id', $this->order_id)->update($userBalanceLogUpdateData);
                }
                break;
            // 取消
            case UserBalanceLog::LOG_TYPE_CANCEL:
                $user_balance_log = UserBalanceLog::where('user_id', $user->id)->where('type',$this->type)->where('order_id', $this->order_id)->get();
                if($user_balance_log){
                    $user->balance->decrement('sales_freeze_money', $user_balance_log->sum('money')); // 减去销售冻结金额
                    // 修改日记状态
                    $userBalanceLogUpdateData['log_type'] = $this->log_type;
                    $userBalanceLogUpdateData['remark'] = sprintf("%s 退款%.2f元", $this->user->nickname, $this->order_money);
                    UserBalanceLog::where('user_id', $user->id)->where('order_id', $this->order_id)->update($userBalanceLogUpdateData);
                }
                break;
        }
    }



    // 根据订单id获取订单对应的所有商品
    public function getOrderProductByOrderId($orderId){
        $orderProducts = Order::where('id',$orderId)
            ->with([
                'items' => function($query){
                    $query->with('product');
                },
                'user_coupon' => function($query){
                    $query->with('coupons');
                },
            ])->first();
        return $orderProducts;
    }

    // 计算订单不同会员之间拿货的差价
    public function getUserTypePriceDifference($priceField){
        $orderInfo = $this->order_info;
        $orderItemsData = $orderInfo->items->map(function ($item, $key) use($priceField) {
            $item->total_retail_price_amount = bcmul($item->price,$item->amount,10);
            $item->total_difference_price_amount = bcmul($item->product->$priceField,$item->amount,10);
            return $item;
        });
        // 订单总金额
        $orderTotalAmount = $orderInfo->total_amount;
        // 零售价总金额
        $orderTotalRetailPriceAmount = $orderItemsData->sum('total_retail_price_amount');
        // 当前会员类型拿货价总金额
        $orderTotalDifferencePriceAmount = $orderItemsData->sum('total_difference_price_amount');
        // 如果使用了优惠券，需要计算当前会员类型拿货价总金额使用优惠券后的金额
        if($orderInfo->user_coupon){
            $orderTotalRetailPriceAmount = $orderInfo->user_coupon->getAdjustedPrice($orderTotalRetailPriceAmount);
            $orderTotalDifferencePriceAmount = $orderInfo->user_coupon->getAdjustedPrice($orderTotalDifferencePriceAmount);
        }
        // 差价金额
        $differencePrice = bcsub($orderTotalAmount,$orderTotalDifferencePriceAmount,2);
        return $differencePrice;
    }



    // 计算普通会员销售提成
    protected function handleCommonUser(){
        $configure = Configure::whereIn('name', ['COMMON_SALES_REWARD', 'ADMINISTRATIVE_DIVISION', 'SALES_FACILITATOR_LEVEL'])->pluck('value', 'name')->toArray();
        if($configure){
            // 查找上级用户
            $user_parent = UserRelation::where('to_user_id', $this->user->id)->with('fromUser')->first();

            // 邀请分红，只有上级是普通用户才会有的分红
            if(($user_parent) && ($user_parent->fromUser) && ($user_parent->fromUser->type === User::USER_TYPE_ONE)){
                $this->type = UserBalanceLog::TYPE_SALES;
                $this->handleParentMoney($user_parent->fromUser, $configure['COMMON_SALES_REWARD'],  $this->user->id);
            }
            // 所在服务商分红
            $this->handleFacilitator();
        }
    }

    // 计算代理销售提成
    protected function handleAgent(){
        if($this->user->agent){
            $configure = Configure::whereIn('name', ['SALES_REGION_LEVEL', 'SALES_FACILITATOR_LEVEL'])->pluck('value', 'name')->toArray();
            // 区域代理
            $user_parent_region = UserRelation::where('to_user_id', $this->user->id)->with('fromUser')->first();
            // 邀请分红，如果上级是区域代理商才分
            if(($user_parent_region) && ($user_parent_region->fromUser) && ($user_parent_region->fromUser->type === User::USER_TYPE_THREE)){
                $this->type = UserBalanceLog::TYPE_SALES;
                $this->handleParentMoney($user_parent_region->fromUser, $configure['SALES_REGION_LEVEL'],  $this->user->id);
            }
            // 所在服务商分红
            $this->handleFacilitator();
        }
    }

    // 所在服务商
    protected function handleFacilitator(){
        $configure = Configure::whereIn('name', ['COMMON_SALES_REWARD', 'ADMINISTRATIVE_DIVISION', 'SALES_FACILITATOR_LEVEL'])->pluck('value', 'name')->toArray();
        // 订单地址
        $order = Order::find($this->order_id);
        if($order){
            $address = $order->address;
            if(isset($address['id']) && !empty($address['id'])){
                $user_address = UserAddress::find($address['id']);
                if($user_address){
                    $condition = [];
                    // 按市
                    if(isset($configure['ADMINISTRATIVE_DIVISION']) && $configure['ADMINISTRATIVE_DIVISION'] == Configure::ADMINISTRATIVE_DIVISION_CITY){
                        $condition['city'] = $user_address->city;
                    }else{
                        // 按区县
                        $condition['district'] = $user_address->district;
                    }
                    $facilitators = Store::where($condition)->whereHas('user', function($query){
                        $query->where('type', User::USER_TYPE_FOUR);
                    })->with('user')->get();
                    $facilitators_count = $facilitators->count();
                    if($facilitators_count > 0){
                        $percent = $configure['SALES_FACILITATOR_LEVEL'];
                        // 服务商销售提成平分
                        if($facilitators_count > 1){
                            $percent = bcdiv($percent, $facilitators_count, 10);
                        }
                        $this->type = UserBalanceLog::TYPE_SALES_SERVICE;
                        foreach ($facilitators as $facilitator){
                            if($facilitator->user){
                                $this->handleParentMoney($facilitator->user, $percent, $this->user->id);
                            }
                        }
                    }
                }
            }
        }
    }


    protected  function handleParentMoney($parentUser, $rate, $from_user_id = 0){
            switch ($this->log_type){
                // 冻结
                case UserBalanceLog::LOG_TYPE_FREEZE:
                    $reward = bcdiv(bcmul($this->order_money, $rate, 10), 100, 10);
                    if(bccomp($reward, 0.01, 10) === -1){
                        return ;
                    }
                    $must_reward = round($reward, 2, PHP_ROUND_HALF_UP);
                    // 增加销售冻结金额
                    $parentUser->balance->increment('sales_freeze_money', $must_reward);
                    if(bccomp($must_reward, 0.00, 2) === 1){
                        $parentUser->balanceLogs()->create([
                            'money' => $must_reward,
                            'log_type' => $this->log_type,
                            'type' => $this->type,
                            'remark' => sprintf("%s 拿货%.2f元", $this->user->nickname, $this->order_money),
                            'from_user_id' => $from_user_id,
                            'order_id' => $this->order_id
                        ]);
                    }
                    break;
                // 入账
                case UserBalanceLog::LOG_TYPE_INCOME:
                    $user_balance_log = UserBalanceLog::where('user_id', $parentUser->id)->where('type',$this->type)->where('order_id', $this->order_id)->get();
                    if($user_balance_log){
                        $parentUser->balance->increment('money', $user_balance_log->sum('money')); // 更新用户可用余额
                        $parentUser->balance->increment('sales_money', $user_balance_log->sum('money')); // 更新销售提成奖励
                        $parentUser->balance->increment('total_revenue', $user_balance_log->sum('money')); // 更新总收益
                        $parentUser->balance->decrement('sales_freeze_money', $user_balance_log->sum('money')); // 减去销售冻结金额
                        // 修改用户密钥key
                        event(new UserMoneyEvent($parentUser));
                        // 修改日记状态
                        $user_balance_log->log_type = $this->log_type;
                        $userBalanceLogUpdateData['log_type'] = $this->log_type;
                        UserBalanceLog::where('user_id', $parentUser->id)->where('order_id', $this->order_id)->update($userBalanceLogUpdateData);
                    }
                    break;
                // 取消
                case UserBalanceLog::LOG_TYPE_CANCEL:
                    $user_balance_log = UserBalanceLog::where('user_id', $parentUser->id)->where('type',$this->type)->where('order_id', $this->order_id)->get();
                    if($user_balance_log){
                        $parentUser->balance->decrement('sales_freeze_money', $user_balance_log->sum('money')); // 减去销售冻结金额
                        // 修改日记状态
                        $userBalanceLogUpdateData['log_type'] = $this->log_type;
                        $userBalanceLogUpdateData['remark'] = sprintf("%s 退款%.2f元", $this->user->nickname, $this->order_money);
                        UserBalanceLog::where('user_id', $parentUser->id)->where('order_id', $this->order_id)->update($userBalanceLogUpdateData);
                    }
                    break;
            }
    }
}