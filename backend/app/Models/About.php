<?php

namespace App\Models;

use App\Models\Traits\ImageUrlTrait;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use ImageUrlTrait;
    protected $fillable = ['title', 'image', 'content'];
}
