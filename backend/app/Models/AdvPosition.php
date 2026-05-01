<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvPosition extends Model
{
    protected $fillable = ['name', 'describe', 'flag'];



    public function advs(){
        return $this->hasMany(Advert::class, 'adv_positions_id');
    }


    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return array
     */
    public function scopeSelectOptions($query){
        return $query->orderBy('id', 'desc')->pluck('name', 'id')->toArray();
    }

}
