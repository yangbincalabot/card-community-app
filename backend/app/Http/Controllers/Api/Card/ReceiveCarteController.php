<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/21
 * Time: 14:09
 */

namespace App\Http\Controllers\Api\Card;
use App\Http\Requests\ReceiveCarteDetailRequest;
use App\Http\Resources\ReceiveCarteResource;
use App\Models\ReceiveCarte;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User\Attention;
use Carbon\Carbon;

// 收到的名片
class ReceiveCarteController extends Controller
{
    public function index(Request $request){
        $type = $request->get('type');
        $user = $request->user();
        if($type && in_array($type, [ReceiveCarte::TYPE_SCAN, ReceiveCarte::TYPE_IMPRESS, ReceiveCarte::TYPE_SHARE])){
            $receiveCartes = ReceiveCarte::query()->where('user_id', $request->user()->id)->where('type', $type)->latest()->with(['fromUser' => function($query){
                $query->with('carte');
            }, 'shareUser'])->paginate()->appends(['type' => $type]);
        }else{
            $receiveCartes = ReceiveCarte::query()->where('user_id', $request->user()->id)->latest()->with(['fromUser' => function($query){
                $query->with('carte');
            }])->paginate();
        }

        $threeDayAgo = Carbon::now()->subDays(3)->setTime(0, 0);
        foreach ($receiveCartes as $receiveCarte){
            $receiveCarte->is_within = $receiveCarte->created_at->gte($threeDayAgo); // 是否三天内
            if($receiveCarte->fromUser->carte){
                $tag = Tag::query()->where([
                    'uid' => $user->id,
                    'other_uid' => $receiveCarte->fromUser->id,
                    'type' => Tag::TYPE_OTHER_PERSON,
                    'status' => Tag::STATUS_NORMAL,
                ])->select('title')->first();
                $receiveCarte->fromUser->tag = $tag;
            }
        }

        return new ReceiveCarteResource($receiveCartes);
    }

    public function detail(ReceiveCarteDetailRequest $request){
        $receiveCarte = ReceiveCarte::with(['fromUser', 'shareUser'])->find($request->get('id'));
        $user = $request->user();
        if($user->id !== $receiveCarte->user_id){
            abort(401, '您无权查看');
        }

        // 设置已读
        if($receiveCarte->is_read == ReceiveCarte::UNREAD){
            $receiveCarte->is_read = ReceiveCarte::HAVE_READ;
            $receiveCarte->save();
        }


        // 获取标记信息
        $receiveCarte->fromUser->tag = null;
        if($receiveCarte->fromUser){
            $tag = Tag::query()->where([
                'uid' => $user->id,
                'other_uid' => $receiveCarte->fromUser->id,
                'type' => Tag::TYPE_OTHER_PERSON,
                'status' => Tag::STATUS_NORMAL,
            ])->first();
            $receiveCarte->fromUser->tag = $tag;
        }
        if(!$receiveCarte->fromUser->carte){
            $receiveCarte->fromUser->carte()->create([
                'name' => $receiveCarte->fromUser->nickname,
                'phone' => $receiveCarte->fromUser->phone,
                'avatar' => $receiveCarte->fromUser->avatar,
            ]);
            $receiveCarte->load('fromUser');
        }
        return new ReceiveCarteResource($receiveCarte);
    }

    // 修改标记内容
    public function changeTag(ReceiveCarteDetailRequest $request){
        $receiveCarte = ReceiveCarte::with('fromUser')->find($request->get('id'));
        $user = $request->user();
        if($user->id !== $receiveCarte->user_id){
            abort(401, '非法操作');
        }

        if(!$receiveCarte->fromUser){
            abort(404, '用户不存在');
        }
        Tag::updateOrCreate(
            ['uid' => $user->id, 'other_uid' => $receiveCarte->fromUser->id, 'type' => Tag::TYPE_OTHER_PERSON, 'status' => Tag::TYPE_OWN],
            ['title' => $request->get('tag_title')]);
        return new ReceiveCarteResource($receiveCarte);
    }

    // 检查是否已发送
    public function checkReceiveStatus(Request $request){
        $user_id = (int) $request->get('user_id'); // 名片主的user_id,被接收人
        $status = false; // bool true:表示已发送, false: 未发送


        if($user_id === 0){
            $status = true;
        }else{
            if(!User::find($user_id)){
                abort(404, '用户不存在');
            }
            $fromUser = $request->user(); // 当前用户,即来源id

            if($user_id === $fromUser->id){
                $status = false;
            }
            $receive = ReceiveCarte::where([
                'user_id' => $user_id,
                'from_user_id' => $fromUser->id,
            ])->first();
            if($receive){
                $status = true;
            }
        }

        return new ReceiveCarteResource(compact('status'));
    }

    // 设置收到的名片是否通过
    public function byAdding(Request $request){
        $receiveCarte = ReceiveCarte::query()->findOrFail($request->get('id'));
        $user = $request->user();
        if($receiveCarte->user_id !== $user->id){
            abort(403, '非法操作');
        }

        $adding = $request->get('is_adding');
        $receiveCarte->is_adding = $adding;
        $receiveCarte->save();

        // 通过时存放到名片夹
        if($adding == ReceiveCarte::BY_ADDING){
            if($receiveCarte->fromUser && ($carte = $receiveCarte->fromUser->carte)){
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
            }
        }

        $status = 'ok';
        return new ReceiveCarteResource(compact('status'));
    }

}
