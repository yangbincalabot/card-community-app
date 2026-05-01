<?php


namespace App\Services;


use App\Models\Association;
use App\Models\Membership;

class MembershipService
{
    public function post($aid, $carte_id, $user) {
        $user_id = $user->id;
        $data = compact('user_id', 'aid', 'carte_id');
        $membership = Membership::query()->where($data)->whereIn('status', [Membership::STATUS_UNREVIEWED, Membership::STATUS_PASS])->latest()->first();

        // 如果是企业会员，判断是否是协会创建者或者已经加入改协会

        if ($user->companyCardStatus === true) {
            abort(403, '企业会员勿操作');
        }

        if (Association::query()->where(['user_id' => $user_id, 'aid' => $aid])->exists()) {
            abort(403, '请不要加入自己的协会');
        }

        if ($membership && $membership->status == Membership::STATUS_UNREVIEWED) {
            abort(403,'请勿重复提交');
        }

        if ($membership && $membership->status == Membership::STATUS_PASS) {
            abort(403, '您已是会员');
        }

        $data['status'] = 0;
        return Membership::query()->create($data);
    }
}
