<?php

namespace App\Models\Activity;

use Illuminate\Database\Eloquent\Model;

class ActivityClassification extends Model
{
    protected $table = 'activity_classification';
    protected $fillable = ['title', 'parent_id'];

    public function getParentType($type='') {
        $data = [];
        $data['0'] = '顶级';
        $result = self::where(['parent_id'=>0,'status'=>1])->get();
        if (!$result->isEmpty()) {
            foreach ($result  as $value) {
                $data[$value->id] = '&nbsp;&nbsp;&nbsp;'.$value->title;
            }
        }
        return $data[$type] ?? $data;
    }
}
