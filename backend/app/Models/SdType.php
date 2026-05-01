<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SdType extends Model
{
    protected $table = 'sd_type'; // 供需表

    protected $fillable = ['title', 'parent_id', 'status'];

    public function getParentType($type = '', $other = '') {
        $data = [];
        if (!$other) $data['0'] = '顶级';
        $result = self::where(['parent_id'=>0,'status'=>1])->get();
        if (!$result->isEmpty()) {
            foreach ($result  as $value) {
                $data[$value->id] = ($other ? '' :'&nbsp;&nbsp;&nbsp;').$value->title;
            }
        }
        return $data[$type] ?? $data;
    }

}
