<?php

namespace App\Jobs;

use App\Models\Activity\ActivityApply;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloseActivityApplyOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * CloseActivityApplyOrder constructor.
     * @param ActivityApply $activityApply
     * @param $delay
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
        // 如果订单还是处于未支付状态
        if ($this->order->pay_status == ActivityApply::PAY_STATUS_PENDING) {
            // 通过事务执行 sql
            DB::transaction(function() {
                $updateData['status'] = ActivityApply::STATUS_CANCEL;
                $updateData['pay_status'] = ActivityApply::PAY_STATUS_TIMEOUT;
                $updateData['refund_status'] = ActivityApply::REFUND_STATUS_NOT;
                $this->order->update($updateData);
            });
        }
    }
}
