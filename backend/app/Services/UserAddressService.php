<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/20
 * Time: 10:43
 */

namespace App\Services;


use App\Models\Area;
use App\Models\UserAddress;
use DB;

class UserAddressService
{
    public function add($user, $formData){
        // 是否设置默认地址
        if($formData['is_default'] === UserAddress::IS_DEFAULT_TRUE){
            $user->userAddress()->update(['is_default' => UserAddress::IS_DEFAULT_FALSE]);
        }
        $userAddress = $user->userAddress()->create($formData);
        return  $userAddress;
    }

    public function update($userAddress, $formData){
        try{
            DB::beginTransaction();
            if(isset($formData['is_default']) && boolval($formData['is_default']) === UserAddress::IS_DEFAULT_TRUE && boolval($formData['is_default']) !== $userAddress->is_default){
                $userAddress->user->userAddress()->update(['is_default' => UserAddress::IS_DEFAULT_FALSE]);
            }

            $data = [
                'is_default' => $formData['is_default']
            ];

            if(count($formData) > 2){
                $data['contact_name'] = $formData['contact_name'];
                $data['contact_phone'] = $formData['contact_phone'];
                $data['address'] = $formData['address'];
                $data['province'] = $formData['province'];
                $data['city'] = $formData['city'];
                $data['district'] = $formData['district'];
            }
            $userAddress->update($data);
            DB::commit();
            return $userAddress;
        }catch (\Exception $exception){
            DB::rollBack();
            abort(503, $exception->getMessage());
        }

    }


    private function getAddressInfo($province, $city, $district){
        return Area::whereIn('code', [
            $province, $city, $district
        ])->get()->toArray();
    }
}