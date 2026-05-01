<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\IndustryResource;
use App\Models\Industry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndustryController extends Controller
{
    // 获取行业
    public function index(Request $request){
        $industries = Industry::getIndustries();
        return new IndustryResource($industries);
    }
}
