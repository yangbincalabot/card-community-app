<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/20
 * Time: 14:34
 */

namespace App\Libraries\Creators;


use App\Models\ReceiveCarte;
use Illuminate\Support\Facades\DB;

class ReceiveCarteCreator
{
    public function addReceiveCarte(Array $formData, $user_id){
        DB::beginTransaction();
        try{
            $data = [];
            $data['address_name'] = $formData['address_name'];
            $data['address_title'] = $formData['address_title'];
            $data['latitude'] = $formData['latitude'];
            $data['longitude'] = $formData['longitude'];
            $data['message'] = $formData['message'] ?? '';
            $data['is_adding'] = $formData['is_adding'] ?? ReceiveCarte::NOT_REVIEWED;
            $data['type'] = (isset($formData['type']) && intval($formData['type']) > 1) ? $formData['type'] : ReceiveCarte::TYPE_SCAN;
            if(isset($formData['type']) && intval($formData['type']) > 1){
                $data['type'] = intval($formData['type']);
                if($data['type'] == ReceiveCarte::TYPE_SHARE){
                    $data['share_user_id'] = $formData['from_user_id'];
                }
            }else{
                $data['type'] =  ReceiveCarte::TYPE_SCAN;
            }

            $receiveCarte = ReceiveCarte::firstOrCreate(['user_id' => $user_id, 'from_user_id' => $formData['from_user_id']], $data);
            if(isset($formData['is_scan']) && $formData['is_scan'] === true){
                // 扫码时双方都关注
                // 扫描默认双方都接受
                $data['is_adding'] = ReceiveCarte::BY_ADDING;

                if($data['type'] == ReceiveCarte::TYPE_SHARE){
                    $data['share_user_id'] = $user_id;
                }
                $receiveCarte = ReceiveCarte::firstOrCreate(['user_id' => $formData['from_user_id'], 'from_user_id' => $user_id], $data);
            }
            DB::commit();
            return $receiveCarte;
        }catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            abort(500, '添加名片出错');
        }
    }
}