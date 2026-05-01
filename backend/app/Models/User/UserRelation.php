<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserRelation extends Model
{
    protected $fillable = ['from_user_id', 'to_user_id', 'level', 'path'];

    public function fromUser(){
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(){
        return $this->belongsTo(User::class, 'to_user_id');
    }

}
