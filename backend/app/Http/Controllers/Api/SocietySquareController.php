<?php

namespace App\Http\Controllers\Api;


use App\Http\Resources\CommonResource;
use App\Models\Association;
use App\Services\AssociationsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SocietySquareController extends Controller
{
    // 协会广场
    public function index(Request $request, AssociationsService $service){
        $cardSquares = $service->getList($request);
        return new CommonResource($cardSquares);
    }

    // 协会详情
    public function detail(Request $request){
        $aid = $request->get('aid');
        $association = Association::query()->findOrFail($aid);
        if ($association->status !== Association::STATUS_SUCCESS){
            abort(403, '协会未通过审核');
        }
        $association->load('company');

        return new CommonResource($association);
    }


    // 协会详情(新)
    public function details(Request $request) {
        $aid = $request->get('aid');
        $association = Association::query()->findOrFail($aid);
        if ($association->status !== Association::STATUS_SUCCESS){
            abort(403, '协会未通过审核');
        }
        $association->load('company');

        return new CommonResource($association);
    }

}
