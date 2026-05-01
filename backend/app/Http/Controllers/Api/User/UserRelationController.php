<?php

namespace App\Http\Controllers\Api\User;

use App\Models\User;
use App\Models\User\UserRelation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class UserRelationController extends Controller
{
    // 关系绑定
    public function store(Request $request){
        $user = $request->user();
        $from_user_id = intval($request->get('from_user_id')); // 推荐人id
        $from_user = User::where('id', $from_user_id)->first(); // 推荐人用户信息

        $parent_for_from = UserRelation::where('to_user_id', $from_user_id)->first();

        // 检查被推荐人是否已经有上级，如果已经有上级了，则结束绑定操作
        $userFromInfo = UserRelation::where('to_user_id', $user->id)->first();
        if($userFromInfo){
            Log::info('用户id：'.$user->id.'已经有上级会员，不可再绑定上级会员，如有需要切换上级会员，请通过人工操作绑定');
            return [];
        }
        // 检查被推荐人是否已经有下级，如果已经有下级了就不能再成为其他人的下级，只能通过后台人工操作，结束绑定操作
        $userToInfo = UserRelation::where('from_user_id', $user->id)->first();
        if($userToInfo){
            Log::info('用户id：'.$user->id.'已经有下级会员，不可绑定上级会员，如有需要，请通过人工操作绑定');
            return [];
        }
        // 按树形结构，上下等级不能互绑
        if($from_user && (!$parent_for_from || strpos($parent_for_from->path, sprintf(",%d,", $user->id)) === false)){
            if($user->id !== $from_user_id){

                if($parent_for_from){
                    $level = $parent_for_from->level + 1;
                    $path = sprintf("%s%d,", $parent_for_from->path, $user->id);
                }else{
                    $level = 1;
                    $path = sprintf(",%s,", implode(',', [$from_user_id, $user->id]));
                }

                UserRelation::firstOrCreate([
                    'to_user_id' => $user->id
                ], [
                    'from_user_id' => $from_user_id,
                    'level' => $level,
                    'path' => $path
                ]);
            }
        }
    }
}
