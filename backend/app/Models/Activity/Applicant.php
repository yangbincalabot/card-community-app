<?php

namespace App\Models\Activity;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    // 报名人表
    protected $table = 'activity_applicant';
    protected $fillable = ['user_id', 'region_type', 'name', 'identity_number','phone','sex','birthday'];

    protected $datetimes = ['birthday'];
    // 验证报名人是否适合该组
    const CHECK_STATUS_ONE = 1;
    const CHECK_STATUS_TWO = 2;
    const CHECK_STATUS_THREE = 3;

    // 地区
    const REGION_TYPE_ONE = 1;
    const REGION_TYPE_TWO = 2;

    // 性别
    const SEX_ONE = 1;
    const SEX_TWO = 2;

    // 分组类型
    const GROUP_TYPE_ONE = 1; // 正常年龄组
    const GROUP_TYPE_TWO = 2; // 公开组

    public function getCheckStatus($type = '') {
        $data = [
            self::CHECK_STATUS_ONE => '通过',
            self::CHECK_STATUS_TWO => '该成员性别不符合该组',
            self::CHECK_STATUS_THREE => '该成员年龄不符合该组',
        ];
        return $data[$type] ?? $data;
    }

    public function getRegionType($type = '') {
        $data = [
            self::REGION_TYPE_ONE => '大陆',
            self::REGION_TYPE_TWO => '其他'
        ];
        return $data[$type] ?? $data;
    }

    public function getSex($type = '') {
        $data = [
            self::SEX_ONE => '男',
            self::SEX_TWO => '女'
        ];
        return $data[$type] ?? $data;
    }
}
