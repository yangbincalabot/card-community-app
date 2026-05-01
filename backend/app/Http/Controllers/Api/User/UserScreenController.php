<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\UserScreenResource;
use App\Services\UserScreenService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserScreenController extends Controller
{
    protected $service;
    public function __construct(UserScreenService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request){
        $userScreen = $this->service->list($request->user()->id);
        return new UserScreenResource(compact('userScreen'));
    }

    public function store(Request $request){
        $this->service->store($request->user()->id, $request->all());
    }
}
