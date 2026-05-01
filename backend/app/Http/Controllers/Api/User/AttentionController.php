<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\User\AttentionResource;
use App\Models\Carte;
use App\Models\ReceiveCarte;
use App\Models\User\Attention;
use App\Models\User\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttentionController extends Controller
{


    /*
     *  检查关注状态
     */
    public function show(Attention $attention, Request $request)
    {

        if (!auth('api')->check()) {
            $result = collect();
        } else {
            $user = auth('api')->user();
            $where['uid'] = $user->id;
            $where['from_id'] = $request->input('from_id');
            $result = $attention->where($where)->first();
        }
        return new AttentionResource($result);
    }

    // 用户关注 && 取消关注
    public function store(Request $request)
    {
        $attention = new Attention();
        $carteInfo = Carte::where('uid', Auth::id())->first();
        if (empty($carteInfo)) {
            abort('403','请先创建名片');
        }
        $from_id = $request->input('from_id');
        $data['uid'] = Auth::id();
        $data['from_id'] = $from_id;

        $oldResult = $attention->where($data)->first();
        // 查找对方的信息
        $carteInfo = Carte::where('id', $from_id)->with('user')->first();
        if (!$carteInfo) {
            abort('403','收藏信息不存在，请稍后重试。');
        }
        // 获取名片名字
        $name = $carteInfo->name ?: $carteInfo->user->nickname;
        if (empty($oldResult)) {
            // 用户第一次收藏
            // 将名片首字母存入收藏表
            $data['initial'] = getInitial($name);
            $result = $attention->create($data);
            return new AttentionResource($result);
        }
        // 用户已收藏过，点击取消或者再次收藏
        $status = $attention::ATTENTION_STATUS_TWO;
        if ($oldResult->status == $status) {
            $status = $attention::ATTENTION_STATUS_ONE;
        }
        $updateData['status'] = $status;
        // 将收藏表名片首字母更新
        $updateData['initial'] = getInitial($name);
        $attention->where('id',$oldResult->id)->update($updateData);
        return new AttentionResource($attention);
    }


    /*
     *  设置关注星级
     */
    public function setStars(Request $request) {
        $cid = $request->get('cid');
        $stars = $request->get('stars');
        $uid = Auth::id();
        $info = Attention::query()->where(['uid' => $uid, 'from_id' => $cid])->first();
        if (!empty($info)) {
            $info->update(['stars' => $stars]);
        }
        $fid = $request->get('fid');
        $receiveInfo = ReceiveCarte::query()->where(['user_id' => $uid, 'from_user_id' => $fid, 'type' => ReceiveCarte::TYPE_SCAN])->first();
        if (!empty($receiveInfo)) {
            $receiveInfo->update(['stars' => $stars]);
        }
        return new AttentionResource(collect());
    }

    // 设置/取消特别关注
    public function setSpecial(Request $request) {
        $cid = $request->get('cid');
//        $special = $request->get('special') ?? 0;
        $user = $request->user();
        $info = Attention::query()->where(['uid' => $user['id'], 'from_id' => $cid])->first();
        if (empty($info)) {
            abort(403, '操作失败,信息为空');
        }
        $special = Attention::ATTENTION_GENERAL;
        if ($special == $info->special) {
            $special = Attention::ATTENTION_SPECIAL;
            $info->special_at = Carbon::now()->toDateTimeString();
        }
        $info->special = $special;
        $info->save();
        return new AttentionResource(collect());
    }

    // 设置联系过
    public function setContact(Request $request) {
        $cid = $request->get('cid');
        $del = $request->get('del', 0);
        $user = $request->user();
        $info = Attention::query()->where(['uid' => $user['id'], 'from_id' => $cid])->first();
        if (empty($info)) {
//            abort(403, '操作失败,信息为空');
            return new AttentionResource(collect());
        }
        $contacted = Attention::ATTENTION_CONTACTED;
        if ($del) {
            $contacted = Attention::ATTENTION_NEVER_CONTACTED;
        }
        $info->contact_at = Carbon::now()->toDateTimeString();
        $info->contacted = $contacted;
        $info->save();
        return new AttentionResource(collect());
    }

    // 设置通话过
    public function setTalk(Request $request) {
        $cid = $request->get('cid');
        $del = $request->get('del', 0);
        $user = $request->user();
        $info = Attention::query()->where(['uid' => $user['id'], 'from_id' => $cid])->first();
        if (empty($info)) {
//            abort(403, '操作失败,信息为空');
            return new AttentionResource(collect());
        }
        $talked = Attention::ATTENTION_TALKED;
        if ($del) {
            $talked = Attention::ATTENTION_NEVER_TALKED;
        }
        $info->talk_at = Carbon::now()->toDateTimeString();
        $info->talked = $talked;
        $info->save();
        return new AttentionResource(collect());
    }

    /*
   *  各个类型的数据分开获取
   *  获取关注的名片列表
   */
    public function getCarteList(Attention $attention, Request $request) {
        $uid = Auth::id();
        $result = $attention->with(['carte' => function ($query) {
            $query->select('id','name','company_name','avatar','phone','position');
        }])
            ->whereHas('carte',function ($query) use ($request) {
                if ($search = $request->input('search', '')) {
                    $like = '%'.$search.'%';
                    $query->where('name', 'like', $like)
                        ->orWhere('company_name', 'like', $like)
                        ->orWhere('position', 'like', $like);
                }
            })
            ->where(['status' => $attention::ATTENTION_STATUS_ONE, 'uid' => $uid])
            ->orderBy('id','desc')
            ->get();
        if ($result->isEmpty()) {
            $result = collect();
        }
        return new AttentionResource($result);

    }


    // 群组名片展示列表
    public function groupCarteList(Request $request) {
        $attention = new Attention();
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['uid'] = Auth::id();
        $result = $attention::with(['carte'])
            ->where($where)
            ->where(function ($query) use ($request) {
                if ($search = $request->input('search','')) {
                    $like = '%'.$search.'%';
                    $query->whereHas('carte',function ($query) use ($like) {
                        $query->where('name', 'like', $like)
                            ->orWhere('company_name', 'like', $like)
                            ->orWhere('position', 'like', $like);
                    });
                }
            })
            ->orderBy('initial','asc')
            ->get();
        $gid = $request->input('gid', '');
        $newData = $attention->getNewResult($result, $gid);
        return new AttentionResource($newData);
    }


    // 群组创建&&更新
    public function groupCreate(Request $request) {
        // 传入id代表更新
        $selectArr = $request->input('selectArr');
        if (empty($selectArr)) {
            abort(403, '创建失败，传入信息不存在');
        }
        if ($id = $request->input('id','')) {
            DB::beginTransaction();
            $updateData = [];
            $updateData['title'] = $request->input('title','新建分组'); // 默认名称
            $updateData['num'] = count($selectArr);
            $groupResult = Group::where('id', $id)->update($updateData);
            if (empty($groupResult)) {
                DB::rollBack();
                abort(403, '更新失败');
            }
            $updateResult = $this->updateAttention($id, $selectArr);
            if (!$updateResult) {
                DB::rollBack();
                abort(403, '更新失败');
            }
            DB::commit();
        } else {
            DB::beginTransaction();
            $createData = [];
            $createData['uid'] = Auth::id();
            $createData['title'] = '新建分组'; // 默认名称
            $createData['num'] = count($selectArr);
            $groupResult = Group::create($createData);
            if (empty($groupResult)) {
                DB::rollBack();
                abort(403, '创建失败');
            }
            $updateResult = $this->updateAttention($groupResult->id, $selectArr);
            if (!$updateResult) {
                DB::rollBack();
                abort(403, '创建失败');
            }
            DB::commit();
            return new AttentionResource($groupResult);
        }

    }

    // 群组主业列表
    public function groupList () {
        $where['uid'] = Auth::id();
        $where['status'] = Group::STATUS_NORMAL;
        $result = Group::where($where)->get();
        if ($result->isEmpty()) {
            $result = collect();
        }
        return new AttentionResource($result);
    }

    // 群组详情列表
    public function groupDetailList (Request $request) {
        $id = $request->input('id');
        if (empty($id)) {
            abort(404, '页面不存在');
        }
        $attention = new Attention();
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['uid'] = Auth::id();
        $result = $attention::with(['carte'])
            ->where($where)
            ->whereHas('carte')
            ->whereRaw("FIND_IN_SET($id, gid)")
            ->orderBy('initial','asc')
            ->get();
        if ($result->isEmpty()) {
            $result = collect();
        }
        return new AttentionResource($result);

    }

    // 临时缓存列表
    public function temporaryList (Request $request) {
        $selectArr = $request->input('selectArr');
        if (!empty($selectArr)) {
            $attention = new Attention();
            $where['status'] = $attention::ATTENTION_STATUS_ONE;
            $where['uid'] = Auth::id();
            $result = Attention::with(['carte'])
                ->where($where)
                ->whereHas('carte',function ($query) use ($selectArr) {
                    $query->whereIn('id', $selectArr);
                })
                ->orderBy('initial','asc')
                ->get();
            if ($result->isEmpty()) {
                $result = collect();
            }
        } else {
            $result = collect();
        }
        return new AttentionResource($result);
    }

    public function groupShow (Request $request) {
        $id = $request->input('id');
        if (empty($id)) {
            abort(404, '页面不存在');
        }
        $result = Group::where('id', $id)->first();
        if (empty($result)) {
            $result = collect();
        }
        return new AttentionResource($result);
    }

    // 将群成员移除该群组
    public function groupRemove (Request $request) {
        $id = $request->input('id');
        $cid = $request->input('cid');
        if (empty($id) || empty($cid)) {
            abort(404, '页面不存在');
        }
        $attention = new Attention();
        $oldResult = $attention->where('id', $cid)->first();
        if (empty($oldResult)) {
            abort(404, '页面不存在');
        }
        $gid = $this->deleteKey($oldResult->gid, $id);
        $newGid = $attention->setGidAttribute($gid);
        DB::beginTransaction();
        $attention->where('id', $oldResult->id)->update(['gid' => $newGid]);
        Group::where('id', $id)->decrement('num');
        DB::commit();
        return new AttentionResource($attention);
    }

    // 根据值删除对应数组元素
    public function deleteKey ($arr, $node) {
        foreach ($arr as $key => $value) {
            if ($value == $node) {
                unset($arr[$key]);
                break;
            }
        }
        return $arr;
    }

    // 更新收藏gid
    public function updateAttention ($gid, $selectArr) {
        if (empty($gid) || empty($selectArr)) {
            abort(403, '创建失败，传入信息不存在');
        }
        $attention = new Attention();

        DB::beginTransaction();
        // 删除其对应原有却未被选中的组别
        $this->deleteOldGroup($gid, $selectArr);
        foreach ($selectArr as $value) {
            $where['from_id'] = $value;
            $where['status'] = $attention::ATTENTION_STATUS_ONE;
            $where['uid'] = Auth::id();
            $res = $attention->where($where)->first();
            if (empty($res)) {
                DB::rollBack();
                abort(403, '创建失败, 请稍后重试');
            }
            $newGid = [];
            if (!empty($res->gid)) {
                $newGid = $res->gid;
            }
            if (is_array($newGid) && !in_array($gid, $newGid)) {
                $newGid[] = $gid;
                $newGid = $attention->setGidAttribute($newGid);
                $attention->where('id', $res->id)->update(['gid' => $newGid]);
            }
        }
        DB::commit();
        return true;
    }



    // 删除其对应原有却未被选中的组别
    public function deleteOldGroup($gid, $selectArr) {
        if (empty($gid) || empty($selectArr)) {
            abort(403, '操作失败，传入信息不存在');
        }
        $attention = new Attention();
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['uid'] = Auth::id();
        $result = $attention->where($where)
            ->whereRaw("FIND_IN_SET($gid, gid)")
            ->get();
        if (!$result->isEmpty()) {
            foreach ($result as $value) {
                if (!in_array($value->from_id, $selectArr)) {
                    $gid = $this->deleteKey($value->gid, $gid);
                    $newGid = $attention->setGidAttribute($gid);
                    $attention->where('id', $value->id)->update(['gid' => $newGid]);
                }
            }

        }
        return true;

    }


    public function groupDelete (Request $request) {
        $id = $request->input('id');
        if (empty($id)) {
            abort(404, '页面不存在');
        }
        $result = $this->getAttentionGroupList($id);
        $attention = new Attention();
        $group = new Group();
        DB::beginTransaction();
        if (!empty($result)) {
            foreach ($result as $value) {
                $gid = $this->deleteKey($value->gid, $id);
                $newGid = $attention->setGidAttribute($gid);
                $attention->where('id', $value->id)->update(['gid' => $newGid]);
            }
        }
        $group->where('id', $id)->delete();
        DB::commit();
        return new AttentionResource($group);
    }


    public function getAttentionGroupList ($id) {
        if (empty($id)) {
            abort(404, '页面不存在');
        }
        $attention = new Attention();
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['uid'] = Auth::id();
        $result = Attention::with(['carte'])
            ->where($where)
            ->whereHas('carte')
            ->whereRaw("FIND_IN_SET($id, gid)")
            ->orderBy('initial','asc')
            ->get();
        return $result;
    }

    /*
     *  选择人员
     */
    public function choose(Request $request) {
        $attention = new Attention();
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['uid'] = Auth::id();
        $result = $attention::with(['carte'])
            ->where($where)
            ->whereHas('carte', function ($query) {
//                $query->where('cid', '<>', 0);
            })
            ->where(function ($query) use ($request) {
                if ($search = $request->input('search','')) {
                    $like = '%'.$search.'%';
                    $query->whereHas('carte',function ($query) use ($like) {
                        $query->where('name', 'like', $like)
                            ->orWhere('company_name', 'like', $like)
                            ->orWhere('position', 'like', $like);
                    });
                }
            })
            ->orderBy('initial','asc')
            ->get();
        $newData = $attention->getNewResult($result);
        return new AttentionResource($newData);
    }


    /*
     *  获取选中的承办人
     */
    public function getUndertakeData(Request $request) {
        $idArr = $request->input('idArr', []);
        if (empty($idArr)) {
            return new AttentionResource(collect());
        }
        $result = Carte::whereIn('id', $idArr)->get();
        if ($result->isEmpty()) {
            return new AttentionResource(collect());
        }
        return new AttentionResource($result);

    }
}
