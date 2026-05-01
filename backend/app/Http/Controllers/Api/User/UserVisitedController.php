<?php

namespace App\Http\Controllers\Api\User;

use App\Models\CarteVisits;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserVisitedController extends Controller
{
    public function index(Request $request){
        $visitors = [];
        $carte = $request->user()->carte;
        if($carte){
           $visitors = CarteVisits::query()->where('carte_id', $carte->id)->where('user_id', '>', 0)->with('user')->latest()->paginate();
        }
        return $visitors;
    }
}
