<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ConfigureResource;
use App\Models\Configure;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConfigureController extends Controller
{
    protected $configure;
    public function __construct(){
        $this->configure = Configure::getConfigureWithArray();
    }

    // 获取协会名称
    public function getSocietyName(){
        $societyName = $this->configure['SOCIETY_NAME'] ?? config('app.name');
        return new ConfigureResource(compact('societyName'));
    }

    // 获取开通企业会员费用
    public function getBusinessCost(){
        $businessCost = $this->configure['BUSINESS_COST'] ?? 0;
        return new ConfigureResource(compact('businessCost'));
    }

    // 获取小程序是否审核
    public function getIsAudit(){
        $isAudit = $this->configure['IS_AUDIT'] ?? Configure::IS_AUDIT_NO;
        return new ConfigureResource(compact('isAudit'));
    }

    // 获取腾讯地图秘钥
    public function getMapApiKey(){
        $mapApiKey = config('app.tencent_map_api_key');
        return new ConfigureResource(compact('mapApiKey'));
    }

    // 获取短信开关状态
    public function getSmsSwitch(){
        $smsSwitch = $this->configure['SMS_SWITCH'] ?? Configure::SMS_CLOSE;
        return new ConfigureResource(compact('smsSwitch'));
    }

}
