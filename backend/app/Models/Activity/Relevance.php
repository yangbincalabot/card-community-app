<?php

namespace App\Models\Activity;

use Illuminate\Database\Eloquent\Model;

class Relevance extends Model
{
    // 报名关联表
    protected $table = 'activity_apply_relevance';
    protected $fillable = ['apply_id','activity_id', 'applicant_id', 'group_id'];

    const STATUS_IN = 1;
    const STATUS_DELETE = 99;

    public function applicant(){
        return $this->belongsTo(RelevanceApplicant::class,'applicant_id');
    }

    public function apply(){
        return $this->belongsTo(ActivityApply::class,'apply_id');
    }

    public function activity(){
        return $this->belongsTo(Activity::class,'activity_id');
    }
}
