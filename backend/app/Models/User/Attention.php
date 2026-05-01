<?php

namespace App\Models\User;

use App\Models\Carte;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Attention extends Model
{
    protected $table = 'user_attention';

    protected $guarded = ['id'];

    // 提交状态：1.已关注；2.已取消
    const ATTENTION_STATUS_ONE = 1;
    const ATTENTION_STATUS_TWO = 2;

    const ATTENTION_SPECIAL = 1; // 特别关注
    const ATTENTION_GENERAL = 0; // 普通关注

    const ATTENTION_CONTACTED = 1; // 联系过
    const ATTENTION_NEVER_CONTACTED = 0; // 没有联系过

    const ATTENTION_TALKED = 1; // 通话过
    const ATTENTION_NEVER_TALKED = 0; // 没有通话过

    // 交换类型
    const EXCHANGE_TYPE_ONE = 1; // 名片广场(默认)
    const EXCHANGE_TYPE_TWO = 2; // 线下扫码
    const EXCHANGE_TYPE_THREE = 3; // 分享链接
    const EXCHANGE_TYPE_FOUR = 4; // 手动添加

    public function getAttentionStatus($type = '') {
        $data = [
            self::ATTENTION_STATUS_ONE => '已关注',
            self::ATTENTION_STATUS_TWO => '已取消'
        ];
        return $data[$type] ?? $data;
    }

    // 数据重组
    public function getNewResult($result, $gid = '') {
        // 数据为空直接返回空的数组
        if ($result->isEmpty()) {
            return [];
        }
        $newData = [];
        $tem = [];
        foreach ($result as $key => $value) {
            if (!empty($tem) && in_array($value->initial,$tem)) {
                $newkey = array_search($value->initial,$tem);
                if (!empty($value->carte) && !empty($value->carte->toArray())) {
                    $dataArr = $value->carte->toArray();
                    $dataArr['gid'] = $value->gid;
                    $dataArr['selected'] = false;
                    if (!empty($value->gid) && !empty($gid) && in_array($gid, $value->gid)) {
                        $dataArr['selected'] = true;
                    }
                    $newData[$newkey]['datas'][] = $dataArr;
                }
            } else {
                $tem[$key] = $value->initial;
                if (!empty($value->carte) && !empty($value->carte->toArray())) {
                    $newData[$key]['alphabet'] = $value->initial;
                    $dataArr = $value->carte->toArray();
                    $dataArr['gid'] = $value->gid;
                    $dataArr['selected'] = false;
                    if (!empty($value->gid) && !empty($gid) && in_array($gid, $value->gid)) {
                        $dataArr['selected'] = true;
                    }
                    $newData[$key]['datas'][] = $dataArr;
                }
            }

        }
        return $newData;
    }

    public function setGidAttribute ($gid) {
        if (!empty($gid) && is_array($gid)) {
            return implode(',',$gid);
        }
        return '';
    }

    public function getGidAttribute ($gid) {
        if (strpos($gid, ',') !== false) {
            return explode(',',$gid);
        }
        return $gid ? [$gid] :'';
    }

    public static function setContactDefault($uid, $cid) {
        if (!$uid || !$cid || $uid == $cid) {
            return '';
        }
        $attentionInfo = self::query()->where(['uid' => $uid, 'from_id' => $cid, 'status' => self::ATTENTION_STATUS_ONE])->first();
        if (!empty($attentionInfo)) {
            $attentionInfo->contacted =  self::ATTENTION_CONTACTED;
            $attentionInfo->contact_at = Carbon::now()->toDateTimeString();
            $attentionInfo->save();
        }
        return '';
    }


    public function carte(){
        $uid = Auth::id();
        return $this->belongsTo(Carte::class,'from_id')->select('id','uid','name','company_name','phone','position','email','avatar', 'longitude', 'latitude', 'address_title')->where('uid', '!=', $uid);
    }


    // 判断是否关注过, status单独判断
    public static function checkAttention($uid, $from_id){
        $attention = self::where([
            'uid' => $uid,
            'from_id' => $from_id
        ])->first();
        return $attention;
    }

    // 更改来源用户真实姓名首字母
    public static function changeInitial($from_id, $initial){
        self::where('from_id', $from_id)->where('exchange_type', self::ATTENTION_STATUS_ONE)->update(['initial' => $initial]);
    }

}
