<?php

namespace App\Models\Activity;

use Illuminate\Database\Eloquent\Model;

class RelevanceApplicant extends Model
{
    // 报名人表
    protected $table = 'activity_relevance_applicant';
    protected $fillable = ['user_id', 'region_type', 'name', 'identity_number','phone','sex','birthday'];

    protected $dates = ['birthday'];
    // 地区
    const REGION_TYPE_ONE = 1;
    const REGION_TYPE_TWO = 2;

    public function getRegionType($type = '') {
        $data = [
            self::REGION_TYPE_ONE => '大陆',
            self::REGION_TYPE_TWO => '其他'
        ];
        return $data[$type] ?? $data;
    }
}
