<?php

namespace App\Providers;

use App\Models\Activity\ActivityApply;
use App\Models\Configure;
use App\Models\PlatformIncome;
use App\Models\User\UserBalance;
use App\Models\User\UserBalanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class OrderCommissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $carbon = new Carbon();
        $configure = new Configure();
        $settle_rate = $configure->getConfigure('SETTLE_RATE');
        if ($settle_rate < 0 || $settle_rate > 100) {
            return false;
        }
        $settle_time = $configure->getConfigure('SETTLE_TIME');
        // 今天减去结算时间作为条件
        $newTime = $carbon->now();
        if ($settle_time > 0) {
            $newTime = $newTime->parse("-$settle_time days")->toDateTimeString();
        }
        $applyModel = new ActivityApply();
        $where['status'] = $applyModel::STATUS_COMPLETED;
        $where['pay_status'] = $applyModel::PAY_STATUS_PAID;
        $where['refund_status'] = $applyModel::REFUND_STATUS_REFUNDABLE;
        $where['commission_status'] = $applyModel::COMMISSION_STATUS_TWO;
        $result = $applyModel->with(['activity' => function ($query) {
                $query->select('id', 'uid', 'activity_time');
            }])
            ->where($where)
            ->where('price', '>', 0)
            ->whereHas('activity', function ($query) use ($newTime) {
                $query->where('activity_time', '<', $newTime)->where('shelves_status',1);
            })
            ->get();
        Log::info(count($result));
        $platformIncome = new PlatformIncome();
        $userBalanceLog = new UserBalanceLog();
        $userBalance = new UserBalance();
        if ($result->isNotEmpty()) {
            foreach ($result as $item) {
                if (empty($item->activity)) {
                    continue;
                }
                $uid = $item->activity->uid;
                $platformIncomeResult = $platformIncome->where(['user_id' => $uid, 'type' => $platformIncome::ACTIVE_TYPE, 'info_id' => $item->id])->first();
                if (!empty($platformIncomeResult)) {
                    continue;
                }
                $balanceLogResult = $userBalanceLog->where(['user_id' => $uid, 'log_type' => $userBalanceLog::LOG_TYPE_INCOME, 'activity_id' => $item->aid, 'apply_id' => $item->id])->first();
                if (!empty($balanceLogResult)) {
                    continue;
                }
                $platform_revenue = $item->price * ($settle_rate/100); // 平台收益
                // 如果平台收益记录为空
                $platformIncome->addPlatformIncome($uid, $platformIncome::ACTIVE_TYPE, $platform_revenue, $item->id, $remark = '报名平台收益');
                // 如果该发起者没有该活动的收益
                $addBalanceMoney = $item->price - $platform_revenue; // 发起者添加余额
                $userBalanceLog::addLog($uid, $userBalanceLog::LOG_TYPE_INCOME, $userBalanceLog::TYPE_ACTIVITY_REWARD, $addBalanceMoney, '活动报名收入', $item->aid, $item->id);
                $userBalance::where('user_id', $uid)->increment('money', $addBalanceMoney);
                // 将分佣状态改为已分佣
                $applyModel->where('id', $item->id)->update(['commission_status' => $applyModel::COMMISSION_STATUS_ONE]);
            }
        }

    }
}
