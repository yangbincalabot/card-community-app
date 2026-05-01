<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


//  系统配置
class Configure extends Model
{
    protected $fillable = ['name', 'value'];

    // 小程序是否处于审核状态
    const IS_AUDIT_NO = 1; // 非审核状态，正式上线
    const IS_AUDIT_YES = 2; // 审核状态

    // 发布活动是否需要审核
    const ACTIVITY_VERIFY_NO = 1; // 不需要审核，发布后直接显示
    const ACTIVITY_VERIFY_YES = 2; // 需要审核，发布后审核通过才显示

    // 发布供需是否需要审核
    const SUPPLY_DEMAND_NO = 1; // 不需要审核，发布后直接显示
    const SUPPLY_DEMAND_YES = 2; // 需要审核，发布后审核通过才显示

    // 是否开启短信（目前主要用于支付密码设置）
    const SMS_OPEN = 1; // 开启短信
    const SMS_CLOSE = 2; // 关闭短信







    /**
     * 后台加载公共配置时使用
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return array
     */
    public function scopeBackground($query){
        return $query->pluck('value', 'name')->toArray();
    }

    /**
     * 获取配置信息
     * @param null $name
     * @param null $default
     * @return mixed|null
     */
    public function getConfigure($name = null, $default = null){
        $configure = self::where('name', $name)->first();
        return $configure ? $configure->value : $default;
    }

    public static function getAudits(){
        return [
            self::IS_AUDIT_NO => '正式上线',
            self::IS_AUDIT_YES => '审核状态(测试)'
        ];
    }

    public static function getActivity(){
        return [
            self::ACTIVITY_VERIFY_NO => '不审核',
            self::ACTIVITY_VERIFY_YES => '审核'
        ];
    }

    public static function getSupplyDemand(){
        return [
            self::SUPPLY_DEMAND_NO => '不审核',
            self::SUPPLY_DEMAND_YES => '审核'
        ];
    }

    public static function getSmsSwitch(){
        return [
            self::SMS_OPEN => '开启',
            self::SMS_CLOSE => '关闭'
        ];
    }




    public static function getConfigureWithArray(){
        return self::pluck('value', 'name')->toArray();
    }

    /**
     * 获取具体配置项
     * @param $name
     * @param null $default
     * @return string|null
     */
    public static function getValue($name, $default = null){
        $configure = self::where('name', $name)->first();
        return $configure ? $configure->value : $default;
    }
}
