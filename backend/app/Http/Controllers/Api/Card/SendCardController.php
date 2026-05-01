<?php

namespace App\Http\Controllers\Api\Card;

use App\Http\Resources\SendCardResource;
use App\Models\Carte;
use App\Models\ReceiveCarte;
use App\Models\User\Attention;
use App\Models\User\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SendCardController extends Controller
{
    // 发送名片
    public function send(Request $request){
        $user = $request->user();
        $carte = Carte::query()->findOrFail($request->get('card_id'));
        if($user->id === $carte->uid){
            abort(403, '不能发给自己');
        }

        if(!$user->carte){
            abort(403, '请先创建名片');
        }

        // 查看名片对应的用户是否存在
        if(!$carte->user){
            abort(403, '名片用户不存在');
        }

        $message = $request->get('message');
        if(!$message){
            abort(403, '请输入留言内容');
        }

        // 存放到名片夹
        $userCollection = Attention::where([
            'uid' => $user->id,
            'from_id' => $carte->id,
        ])->first();
        if($userCollection){
            if($userCollection->status !== Attention::ATTENTION_STATUS_ONE){
                $userCollection->status = Attention::ATTENTION_STATUS_ONE;
                $userCollection->save();
            }
        }else{
            Attention::create([
                'uid' => $user->id,
                'from_id' => $carte->id,
                'status' => Attention::ATTENTION_STATUS_ONE,
                'initial' => getInitial($carte->name ?: $user->nickname)
            ]);
        }

        // 查看是否有发送记录

        $receiveCarte = ReceiveCarte::where([
            'user_id' => $carte->uid,
            'from_user_id' => $user->id,
        ])->first();
        if($receiveCarte){
            abort(403, '已递名片');
        }else{
            // 添加记录
            $type = $request->get('type', ReceiveCarte::TYPE_IMPRESS);
             $receiveCarte = ReceiveCarte::create([
                'user_id' => $carte->uid,
                'from_user_id' => $user->id,
                'type' => ReceiveCarte::TYPE_IMPRESS,
                 'message' => $message,
                 'is_adding' => ReceiveCarte::NOT_REVIEWED,
                 'type' => $type,
                 'share_user_id' => $type == ReceiveCarte::TYPE_SHARE ? $request->get('share_user_id', 0) : 0
            ]);
        }
        return new SendCardResource($receiveCarte);

    }
}
