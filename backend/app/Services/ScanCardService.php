<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/8
 * Time: 16:19
 */

namespace App\Services;
use App\Models\ScanLog;
use Illuminate\Support\Str;

class ScanCardService
{

    // 解析名片
    public function resolveCard($file_path){
        $cardGateways = ucfirst(Str::camel(config('card.default')));
        try{
            $cardScanClass = app('App\Services\Scan\\' . $cardGateways);
            // 添加扫描记录
            ScanLog::addScanLog(request()->user('api')->id, config('card.default'));
            return $cardScanClass->resolve($file_path);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, $e->getMessage());
        }
    }
}