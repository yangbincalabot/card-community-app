<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\LikeResource;
use App\Models\Like;
use App\Models\Supply;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{


    /*
     * 点赞，取消点赞操作
     */
    public function store(Like $like, Request $request)
    {
        $type = $request->input('type') ?: $like::TYPE_SUPPLY;
        $info_id = $request->input('info_id');
        $user_id = Auth::id();
        // 目前只有供需
        if ($type != $like::TYPE_SUPPLY) {
            abort(403,'不存在的类型错误');
        }
        if (empty($info_id)) {
            abort(403, '点赞错误，不存在该条信息');
        }
        $oldRes = $like->where(['uid'=>$user_id, 'type'=>$type, 'info_id'=>$info_id])->first();
        //用户点击收藏操作
        if (!empty($oldRes)) {
            DB::beginTransaction();
            $status = $like::STATUS_TWO;
            if ($oldRes->status == $status) {
                $status = $like::STATUS_ONE;
                // 供需点赞数加1
                Supply::where('id',$info_id)->increment('likes');
            } else {
                // 供需点赞数减1
                Supply::where('id',$info_id)->decrement('likes');
            }
            $like->where('id',$oldRes->id)->update(['status'=>$status]);
            DB::commit();
            return new LikeResource($like);
        }
        DB::beginTransaction();
        $createData['uid'] = $user_id;
        $createData['info_id'] = $info_id;
        $createData['type'] = $type;
        $result = $like->create($createData);
        // 供需点赞数加1
        Supply::where('id',$info_id)->increment('likes');
        DB::commit();
        return new LikeResource($result);
    }

    /*
     * 获取点赞状态
     */
    public function show(Like $like, Request $request)
    {
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $user_id = $user->id;
            $type = $request->input('type')?: $like::TYPE_SUPPLY;
            $info_id = $request->input('info_id');
            $where['uid'] = $user_id;
            $where['type'] = $type;
            $where['info_id'] = $info_id;
            $result = $like->where($where)->first();
            if (!empty($result)) {
                return new LikeResource($result);
            }
        }
        return new LikeResource(collect());
    }


}
