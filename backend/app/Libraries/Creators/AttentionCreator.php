<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/20
 * Time: 14:41
 */

namespace App\Libraries\Creators;


use App\Models\User;
use App\Models\User\Attention;
use Illuminate\Support\Facades\DB;

class AttentionCreator
{
    // 名片夹
    public function addCard($formData, $user_id){
        DB::beginTransaction();
        try{
            $data = [];
            $data['uid'] = $user_id;
            $data['status'] = Attention::ATTENTION_STATUS_ONE; // 收藏
            $data['gid'] = 0;
            $data['exchange_type'] = $formData['exchange_type'] ?? Attention::EXCHANGE_TYPE_TWO;  // 交换类型, 2-线下扫码， 3-分享链接

            // 判断被扫描者是否有名片
            $fromUser = User::find($formData['to_uid']);
            // 对方名片
            $fromUserCarte = $fromUser->carte;
            if($fromUserCarte){
                $data['initial'] = getInitial($fromUserCarte->name ?: $fromUser->nickname); // 来源用户真实姓名首字母
            }else{
                $data['initial'] = getInitial($fromUser->nickname ?? '#'); // 来源用户真实姓名首字母
                // 添加默认名片
                $fromUserCarte = $fromUser->carte()->create([
                    'name' => $fromUser->nickname,
                    'phone' => $fromUser->phone,
                    'avatar' => $fromUser->avatar,
                ]);
            }
            $data['from_id'] = $fromUserCarte->id; // 名片id

            // 判断是否收藏过(from_id为对方的名片id)
            $attention = Attention::checkAttention($data['uid'], $fromUserCarte->id);
            if($attention && $attention->status !== Attention::ATTENTION_STATUS_ONE){
                $attention->status = Attention::ATTENTION_STATUS_ONE;
                $attention->save();
            }elseif(!$attention){
                $attention = Attention::create($data);
            }

            // 检查首字母
            if($attention->initial !== $data['initial']){
                Attention::changeInitial($data['from_id'], $data['initial']);
            }


            DB::commit();
            return $attention;
        }catch (\Exception $e){
            DB::rollBack();
            \Log::error($e->getFile() . '--' . $e->getLine() . '--' . $e->getMessage());
            abort(500, '添加名片出错');
        }

    }
}
