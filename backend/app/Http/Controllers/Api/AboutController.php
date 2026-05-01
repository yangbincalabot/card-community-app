<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AboutResource;
use App\Models\About;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    public function getDetail(){
        $about = About::first();
        return new AboutResource($about);
    }
}
