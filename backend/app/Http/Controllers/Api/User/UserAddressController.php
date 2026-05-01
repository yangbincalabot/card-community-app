<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\UserAddressResource;
use App\Models\UserAddress;
use App\Services\UserAddressService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserAddressController extends Controller
{

    public function index(Request $request){
        return new UserAddressResource($request->user()->getUserAddress());
    }

    public function add(Request $request, UserAddressService $userAddressService){
        $formData = [
            'contact_name' => $request->get('contact_name'),
            'contact_phone' => $request->get('contact_phone'),
            'province' => $request->get('province'),
            'city' => $request->get('city'),
            'district' => $request->get('district'),
            'address' => $request->get('address'),
            'is_default' => (boolean) $request->get('is_default')
        ];
        $userAddress = $userAddressService->add($request->user(), $formData);
        return new UserAddressResource($userAddress);
    }

    public function show(Request $request){
        $userAddress = UserAddress::find($request->get('id'));
        $this->authorize('own', $userAddress);
        return new UserAddressResource($userAddress);
    }


    public function update(Request $request, UserAddressService $userAddressService){
        $userAddress = UserAddress::where('id', $request->get('id'))->first();
        $this->authorize('own', $userAddress);
        return new UserAddressResource($userAddressService->update($userAddress, $request->all()));
    }

    public function delete(Request $request){
        $userAddress = UserAddress::where('id', $request->get('id'))->first();
        $this->authorize('own', $userAddress);
        $userAddress->delete();
        return new UserAddressResource($userAddress);
    }
}
