<?php

namespace App\Models;

use App\Models\Traits\ImageUrlTrait;
use Illuminate\Database\Eloquent\Model;


// 系统公告
class Communal extends Model
{
    use ImageUrlTrait;

    protected $fillable = ['title', 'content', 'image'];


}
