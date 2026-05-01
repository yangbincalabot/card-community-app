<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StoreController extends Controller
{
    public function index(Request $request, StoreService $storeService){

        $stores = $storeService->getLatelyStore($request->all());
        return new StoreResource($stores);
    }


    public function detail(Request $request){
        $id = $request->get('id');
        $store = Store::where('id', $id)->with('user')->find($id);
        return new StoreResource($store);
    }

    public function getUserStoreDetail(Request $request){
        return new StoreResource($request->user()->store);
    }

    public function updateStore(Request $request){
        $store = $request->user()->store;

        $store->contact_name = $request->get('contact_name');
        $store->contact_mobile = $request->get('contact_mobile');
        $store->image = $request->get('image');
        $store->save();
        return new StoreResource($store);
    }
}
