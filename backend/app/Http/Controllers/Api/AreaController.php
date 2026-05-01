<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AreaResourcce;
use App\Models\Area;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AreaController extends Controller
{

    // 获取所有省份名称
    public function provinces(){
        $provinces = Area::where('parent_id', Area::PROVINCE)->pluck('name');
        return new AreaResourcce($provinces);
    }

    // 获取所有省市
    public function areas(){
        $areas = Area::query()->where('parent_id', Area::PROVINCE)->with(['children'])->get();
        return new AreaResourcce($areas);
    }
}
