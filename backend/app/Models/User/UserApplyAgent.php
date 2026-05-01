<?php

namespace App\Models\User;

use App\Models\Agent;
use App\Models\Traits\AreaTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserApplyAgent extends Model
{
    use AreaTrait;
    protected $fillable = [
        'user_id', 'agent_id', 'name', 'mobile', 'id_card', 'province', 'city', 'district', 'address', 'status', 'remark', 'full_address'
    ];

    const APPLY_STATUS_SUCCESS = 1; // 审核通过
    const APPLY_STATUS_STAY = 2; // 待审核
    const APPLY_STATUS_FAIL = 3; // 审核失败

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent(){
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public static function getStatus($status = ''){
        $data = [
            self::APPLY_STATUS_SUCCESS => '审核通过',
            self::APPLY_STATUS_STAY => '待审核',
            self::APPLY_STATUS_FAIL => '审核失败'
        ];
        return $data[$status] ?? $data;
    }

    // 获取审核通过的店中店和服务商
    public static function getStoreSelectOptions(){
        $options = [];
        $userApplyAgent = self::query()->where('status', self::APPLY_STATUS_SUCCESS)->with(['user' => function($query){
            $query->whereIn('type', [User::USER_TYPE_TWO, User::USER_TYPE_FOUR]);
        }])->get();
        foreach ($userApplyAgent as $item){
            $options[$item->user_id] = sprintf("申请人姓名：%s (手机：%s)", $item->name, $item->mobile);
        }
        return $options;
    }



}
