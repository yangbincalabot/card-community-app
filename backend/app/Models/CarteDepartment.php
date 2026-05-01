<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarteDepartment extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public static function addCarteDepartment($uid, $department_id, $carte_id){
        return self::query()->firstOrCreate(compact('uid', 'department_id', 'carte_id'));
    }

    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }
}
