<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\User\CollectionResource;
use App\Models\Activity\Activity;
use App\Models\Carte;
use App\Models\Supply;
use App\Models\User;
use App\Models\User\Collection;
use App\Models\User\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Collection  $collection, Request $request)
    {
        $type = $request->input('type');
        $typeArr = [$collection::COLLECTION_TYPE_TWO, $collection::COLLECTION_TYPE_THREE];
        if (!in_array($type, $typeArr)) {
            abort('403','收藏类型不存在，请稍后重试。');
        }
        $info_id = $request->input('info_id');
        if (empty($info_id)) {
            abort('403','收藏信息不存在，请稍后重试');
        }
        $data = [];
        $data['uid'] = Auth::id();
        $data['info_id'] = $info_id;
        $data['type'] = $type;
        $oldResult = $collection->where($data)->first();
        if (empty($oldResult)) {
            // 用户第一次收藏
            $result = $collection->create($data);
            return new CollectionResource($result);
        }
        // 用户已收藏过，点击取消或者再次收藏
        $status = $collection::COLLECTION_STATUS_TWO;
        if ($oldResult->status == $status) {
            $status = $collection::COLLECTION_STATUS_ONE;
        }
        $updateData['status'] = $status;
        $collection->where('id',$oldResult->id)->update($updateData);
        return new CollectionResource($collection);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function show(Collection $collection, Request $request)
    {
        $result = collect();
        if ($user = request()->user('api')) {
            $user = auth('api')->user();
            $where['uid'] = $user->id;
            $where['type'] = $request->input('type');
            $where['info_id'] = $request->input('info_id');
            $result = $collection->where($where)->first();
        }
        return new CollectionResource($result);
    }


    /*
     *  获取收藏的供需列表
     */
    public function getSupplyList(Collection $collection, Request $request) {
        $type = $collection::COLLECTION_TYPE_TWO;
        if ($request->input('type') != $type) {
            abort(404,'获取失败，请稍后重试');
        }
        $supplyModel = new Supply();
        $uid = Auth::id();
        $result = $collection->with(['supply' => function ($query) {
                $query->select('id','uid','type','content')->with(['carte' => function ($query) {
                    $query->select('id','uid','name','company_name','avatar','phone','position');
                }]);
            }])
            ->where(function ($query) use ($supplyModel) {
                $query->whereHas('supply',function ($query) use ($supplyModel) {
                    $query->where('status',$supplyModel::STATUS_PASSED)->whereHas('carte');
                });
            })
            ->where(['status' => $collection::COLLECTION_STATUS_ONE, 'uid' => $uid])
            ->latest()
            ->paginate(10);
        return new CollectionResource($result);

    }

    /*
     *  获取收藏的活动列表
     */
    public function getActivityList(Collection $collection, Request $request) {
        $type = $collection::COLLECTION_TYPE_THREE;
        if ($request->input('type') != $type) {
            abort(404,'获取失败，请稍后重试');
        }
        $uid = Auth::id();
        $activityModel = new Activity();
        $result = $collection->with(['activity' => function ($query) {
                $query->select('id','uid','cover_image','title','activity_time','apply_end_time','type');
            }])
            ->whereHas('activity',function ($query) use ($activityModel) {
                $query->where('status',$activityModel::STATUS_PASSED);
            })
            ->where(['status' => $collection::COLLECTION_STATUS_ONE, 'uid' => $uid])
            ->latest()
            ->paginate(10);
        return new CollectionResource($result);

    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function edit(Collection $collection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Collection $collection)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collection $collection)
    {
        //
    }
}
