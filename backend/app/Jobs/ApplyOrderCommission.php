<?php

namespace App\Jobs;

use App\Models\Activity\Activity;
use App\Models\Activity\ActivityApply;
use App\Models\Configure;
use App\Models\PlatformIncome;
use App\Models\User\UserBalance;
use App\Models\User\UserBalanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplyOrderCommission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($activityApply, $delay)
    {
        $this->order = $activityApply;
        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $aid = $this->order->aid;
        $price = $this->order->price;
        $applyModel = new ActivityApply();
        if ($this->order->status == $applyModel::STATUS_COMPLETED && $this->order->pay_status == $applyModel::PAY_STATUS_PAID && $this->order->refund_status == $applyModel::REFUND_STATUS_REFUNDABLE) {
            if ($this->order->commission_status == $applyModel::COMMISSION_STATUS_ONE) {
                // 该订单已分佣
                return false;
            }
            if (!$price || $price == 0.00) {
                Log::info('报名订单为'.$this->order->id.' ,活动费用异常');
                return false;
            }
            $activity = Activity::where('id', $aid)->first();
            if (empty($activity)) {
                Log::info('报名订单为'.$this->order->id.' ,活动不存在');
                return false;
            }
            $uid = $activity->uid; // 该uid为活动发起者的uid
            $UserBalanceLog = new UserBalanceLog();
            $configure = new Configure();
            $settle_rate = $configure->getConfigure('SETTLE_RATE');
            if ($settle_rate < 0 || $settle_rate > 100) {
                Log::info('报名订单为'.$this->order->id.' ,结算比例异常');
                return false;
            }
            DB::beginTransaction();
            try {
                $platformIncome = new PlatformIncome();
                $platform_revenue = $price * ($settle_rate/100); // 平台收益
                $platformIncomeResult = $platformIncome->where(['user_id' => $uid, 'type' => $platformIncome::ACTIVE_TYPE, 'info_id' => $this->order->id])->first();
                if (empty($platformIncomeResult)) {
                    // 如果平台收益记录为空
                    $platformIncome->addPlatformIncome($uid, $platformIncome::ACTIVE_TYPE, $platform_revenue, $this->order->id, $remark = '报名平台收益');
                }
                $balanceLogResult = $UserBalanceLog->where(['user_id' => $uid, 'log_type' => $UserBalanceLog::LOG_TYPE_INCOME, 'activity_id' => $aid, 'apply_id' => $this->order->id])->first();
                if (empty($balanceLogResult)) {
                    // 如果该发起者没有该活动的收益
                    $addBalanceMoney = $price - $platform_revenue; // 发起者添加余额
                    $UserBalanceLog::addLog($uid, $UserBalanceLog::LOG_TYPE_INCOME, $UserBalanceLog::TYPE_ACTIVITY_REWARD, $addBalanceMoney, '活动报名收入', $aid, $this->order->id);
                    UserBalance::where('user_id', $uid)->increment('money', $addBalanceMoney);
                }
                // 将分佣状态改为已分佣
                $this->order->update(['commission_status' => $applyModel::COMMISSION_STATUS_ONE]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }

        }

    }
}
