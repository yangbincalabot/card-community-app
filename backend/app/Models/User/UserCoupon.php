<?php

namespace App\Models\User;

use App\Exceptions\CouponCodeUnavailableException;
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    protected $table = 'user_coupons';     // 数据表名
    public static $snakeAttributes = false;   // 设置关联模型在打印输出的时候是否自动转为蛇型命名
    protected $guarded = ['id'];        // 过滤的字段

    protected $dates = ['used_at'];

    const STATUS_USED = 1;
    const STATUS_NOT_USE = 2;

    public function coupons(){
        return $this->belongsTo(CouponCode::class,'coupon_id');
    }

    public function checkAvailable(User $user, $orderAmount = null)
    {
        if (!$this->coupons->enabled) {
            abort(403,'优惠券不存在');
        }

        if ($this->coupons->total - $this->coupons->used <= 0) {
            abort(403,'该优惠券已被兑完');
        }

        if ($this->coupons->not_before && $this->coupons->not_before->gt(Carbon::now())) {
            abort(403,'该优惠券现在还不能使用');
        }

        if ($this->coupons->not_after && $this->coupons->not_after->lt(Carbon::now())) {
            abort(403,'该优惠券已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->coupons->min_amount) {
            abort(403,'订单金额不满足该优惠券最低金额');
        }

        $used = Order::where('user_id', $user->id)
            ->where('user_coupon_id', $this->id)
            ->where(function($query) {
                $query->where(function($query) {
                    $query->whereNull('paid_at')
                        ->whereNull('confirm_offline_pay_at')
                        ->where('closed', false);
                })->orWhere(function($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', '!=', Order::REFUND_STATUS_SUCCESS);
                });
            })
            ->exists();
        if ($used) {
            abort(403,'你已经使用过这张优惠券了');
        }
    }

    public function getAdjustedPrice($orderAmount)
    {
        // 固定金额
        if ($this->coupons->type === CouponCode::TYPE_FIXED) {
            // 为了保证系统健壮性，我们需要订单金额最少为 0.01 元
            return max(0.01, $orderAmount - $this->coupons->value);
        }

        return number_format($orderAmount * $this->coupons->value / 100, 2, '.', '');
    }


    public function changeUsed($increase = true)
    {
        // 传入 true 代表新增用量，否则是减少用量
        if ($increase) {
            // 与检查 SKU 库存类似，这里需要检查当前用量是否已经超过总量
            $where['id'] = $this->id;
            $where['status'] = UserCoupon::STATUS_NOT_USE;
            return $this->where($where)->update(['status'=>UserCoupon::STATUS_USED]);
        } else {
            $where['id'] = $this->id;
            $where['status'] = UserCoupon::STATUS_USED;
            return $this->where($where)->update(['status'=>UserCoupon::STATUS_NOT_USE]);
        }
    }
}
