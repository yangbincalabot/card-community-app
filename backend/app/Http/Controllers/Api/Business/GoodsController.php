<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Requests\Business\GoodsRequest;
use App\Http\Resources\CommonResource;
use App\Models\CompanyCard;
use App\Models\User;
use App\Services\GoodsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class GoodsController extends Controller
{
    protected $server;

    public function __construct(GoodsService $service)
    {
        $this->server = $service;
    }

    public function index(Request $request){
        $user = $request->user();
        $goods = $this->server->list([], $user);
        return new CommonResource(compact('goods'));
    }

    public function add(GoodsRequest $request){
        $formData = $request->all();
        $user = $request->user();
        $this->server->add($formData, $user);
        $status = Response::HTTP_OK;
        return new CommonResource(compact('status'));
    }

    public function update(GoodsRequest $request){
        $updateGoods = $request->all();
        $user = $request->user();
        $id = $request->get('id');
        $this->server->update($updateGoods, $id, $user);
        $status = Response::HTTP_OK;
        return new CommonResource(compact('status'));
    }

    public function delete(Request $request){
        $id = $request->get('id');
        $user = $request->user();
        $this->server->delete($id, $user);
        $status = Response::HTTP_OK;
        return new CommonResource(compact('status'));
    }

    public function show(Request $request){
        $id = $request->get('id');
        $goods = $this->server->detail($id);
        return new CommonResource(compact('goods'));
    }


}
