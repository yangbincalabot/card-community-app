<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CommunalResource;
use App\Models\Communal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommunalController extends Controller
{

    public function getCommunals(Request $request){
        $communals = Communal::latest()->paginate();
        foreach ($communals as $communal){
            $communal->content = msubstr(strip_tags($communal->content), 0, 15);
        }
        return new CommunalResource($communals);
    }

    public function getCommunalDetail(Request $request){
        $communal = Communal::where('id', $request->get('id'))->firstOrFail();
        return new CommunalResource($communal);
    }
}
