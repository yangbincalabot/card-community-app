<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'name', 'price', 'introduce', 'sort', 'type'
    ];


    public function setIntroduceAttribute($value){
        $this->attributes['introduce'] = json_encode(explode("\n", $value));
    }

    public function getIntroduceAttribute($value){
        return implode("\n", json_decode($value, true));
    }

    public static function getSelectOptions(){
        return self::orderBy('sort', 'desc')->orderBy('id', 'desc')->pluck('name', 'id')->toArray();
    }
}
