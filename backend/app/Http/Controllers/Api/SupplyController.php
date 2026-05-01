<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SupplyRequest;
use App\Http\Resources\SupplyResource;
use App\Models\Association;
use App\Models\Configure;
use App\Models\Like;
use App\Models\SdType;
use App\Models\Supply;
use App\Models\User\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplyController extends Controller
{

    public function create(SupplyRequest $request) {
        $supplyModel = new Supply();
        $configure = new Configure();
        $requestData = $request->all();
        $user = Auth::user();
        $createData['uid'] = $user['id'];
        $createData['type'] = $requestData['type'];
        $createData['content'] = $requestData['content'];
        $createData['images'] = $requestData['images'];
        $reviewStatus = $configure->getConfigure('SUPPLY_DEMAND');
        // 检测输入文本是否合法
        $secMsg = $requestData['content'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        if ($reviewStatus == $configure::SUPPLY_DEMAND_YES) $createData['status'] = $supplyModel::STATUS_UNDER_REVIEW;
        $result = $supplyModel->create($createData);
        return new SupplyResource($result);
    }

    public function update(SupplyRequest $request) {
        $supplyModel = new Supply();
        $configure = new Configure();
        $requestData = $request->all();
        $id = $requestData['id'];
        $this->checkData($id);
        $createData['type'] = $requestData['type'];
        $createData['content'] = $requestData['content'];
        $createData['images'] = $supplyModel->setImagesAttribute($requestData['images']);
        $reviewStatus = $configure->getConfigure('SUPPLY_DEMAND');
        // 检测输入文本是否合法
        $secMsg = $requestData['content'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        if ($reviewStatus == $configure::SUPPLY_DEMAND_YES) $createData['status'] = $supplyModel::STATUS_UNDER_REVIEW;
        $supplyModel->where('id',$id)->update($createData);
        return new SupplyResource($supplyModel);
    }



    // 编辑时获取内容接口
    public function show(Supply $supply,Request $request) {
        $id = $request->get('id');
        $this->checkDetailId($id);
        $result = $supply->where('id',$id)->first();
        return new SupplyResource($result);
    }

    // 详情页接口
    public function getDetail (Supply $supply,Request $request) {
        $id = $request->get('id');
        $this->checkDetailId($id);
        // 目前不做限制浏览量加1
        $supply->where('id',$id)->increment('visits');
        $result = $supply->with(['carte'])
            ->where('id',$id)
            ->where(function ($query){
                $query->whereHas('carte');
            })
            ->first();
        return new SupplyResource($result);
    }

    /*
     *  供需增加浏览量
     *  登录后 && 目前本人刷新不增加
     *  其它情况 浏览量+1
     */
    public function addVisits($id) {
        $supply = new Supply();
        $res = $supply->where('id',$id)->first();
        if (empty($res)) {
            return false;
        }
        if (auth('api')->check()) {
            $user = auth('api')->user();
            if ($res->uid == $user->id) {
                return false;
            }
        }
        $supply->where('id',$id)->increment('visits');
    }

    // 获取供需列表页
    public function getList (Supply $supply, Request $request) {
        $query = $supply->with(['carte','self_like'])
            ->where(['status' => $supply::STATUS_PASSED])
            ->where(function ($query) use ($request) {
                $query->whereHas('carte');
                // 判断是否有提交 type 参数，如果有就赋值给 $type变量
                if ($type = $request->input('type', '')) {
                    $query->where('type',$type);
                }
                // search 参数用来模糊搜索供需
                if ($search = $request->input('search', '')) {
                    $like = '%'.$search.'%';
                    $query->where(function ($query) use ($like) {
                        $query->where('content', 'like', $like)
                            ->orWhereHas('carte', function ($query) use ($like) {
                                $query->where('name', 'like', $like)
                                    ->orWhere('company_name', 'like', $like)
                                    ->orWhere('position', 'like', $like);
                            });
                    });
                }
            });
        if (auth('api')->check()) {
            // 用户已经登录了...
            $user  = auth('api')->user();
            $aid = $user->aid;
            if ($aid != 0) {
                $associationInfo = Association::query()->where('id', $aid)->first();
                $pid = $associationInfo->pid;
                if (!$pid || $pid == 0) {
                    $assuid = Association::query()->where('status', Association::STATUS_SUCCESS)
                        ->where(function ($query) use ($aid) {
                            $query->where('pid', $aid)->orWhere('id', $aid);
                        })->pluck('user_id');
                } else {
                    $assuid = Association::query()->where('status', Association::STATUS_SUCCESS)->where(function ($query) use ($pid) {
                        $query->where('id', $pid)->orWhere('pid', $pid);
                    })->pluck('user_id');
                }
                $query->whereIn('uid', $assuid);
            }
        }
        $result = $query->orderBy('id','desc')->paginate(10);
        return new SupplyResource(Supply::buildList($result));
    }



    // 获取自己发布的列表页
    public function getMyList(Supply $supply) {
        $uid = Auth::id();
        $result = $supply->where(['uid' => $uid])
                    ->where('status','!=', $supply::STATUS_DELETED)
                    ->orderBy('id','desc')
                    ->paginate(10);
        return new SupplyResource($result);
    }

    /*
     *  供需删除
     *  将对应的点赞和收藏也删除掉
     */
    public function delete(Supply $supply,Request $request) {
        $id = $request->get('id');
        $this->checkData($id);
        DB::beginTransaction();
        $supply->where('id',$id)->update(['status'=>$supply::STATUS_DELETED]);
        // 将该信息id收藏状态全部改为已取消
        $collection = new Collection();
        $collection->where(['type'=>$collection::COLLECTION_TYPE_TWO,'info_id'=>$id])->update(['status'=>$collection::COLLECTION_STATUS_TWO]);
        // 将该信息id点赞状态全部改为已取消
        $like = new Like();
        $like->where(['type'=>$like::TYPE_SUPPLY,'info_id'=>$id])->update(['status'=>$like::STATUS_TWO]);
        DB::commit();
        return new SupplyResource(collect());
    }

    public function checkDetailId($id) {
        if (!$id) {
            abort(404,'获取数据失败，不存在此条信息');
        }
    }

    public function checkData($id) {
        if (empty($id)) {
            abort(404,'获取数据失败，不存在此条信息');
        }
        $supplyModel = new Supply();
        $res = $supplyModel->where('id',$id)->select('id','uid')->first();
        if (empty($res)) {
            abort(404,'获取数据失败，不存在此条信息');
        }
        if ($res->uid != Auth::id()) {
            abort(403,'您不是该条信息发布者，无法操作');
        }
        return true;
    }


    public function getType() {
        $sdType = new SdType();
        // 暂时默认为一级分类，不考虑父级
        $result = $sdType->where(['status'=>1])->get();
        return  new SupplyResource($result);
    }
}
