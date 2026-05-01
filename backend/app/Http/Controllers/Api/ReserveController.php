<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ReserveResource;
use App\Models\Reserve;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReserveController extends Controller
{
    public function store(Request $request) {
        $aid =  $request->get('aid');
        $cid =  $request->get('cid');
        if (empty($aid) || empty($cid)) {
            abort('403', '预约信息不全');
        }
        $uid = Auth::id();
        $oldRes = Reserve::query()->where(['uid' => $uid, 'aid' => $aid, 'cid' => $cid])->first();
        if (!empty($oldRes)) {
            // 不是首次预约
            $status = Reserve::RESERVE_BOOKED;
            if ($oldRes->status == $status) {
                $status = Reserve::RESERVE_CANCELLED;
            }
            Reserve::query()->where('id', $oldRes->id)->update(['status' => $status]);
            return new ReserveResource(collect());
        }
        $createData['uid'] = $uid;
        $createData['aid'] = $aid;
        $createData['cid'] = $cid;
        $result = Reserve::query()->create($createData);
        return new ReserveResource($result);
    }


    public function reserveList(Request $request) {
        $aid =  $request->get('aid');
        if (empty($aid)) {
            return new ReserveResource(collect());
        }
        if (!auth('api')->check()) {
            return new ReserveResource(collect());
        }
        $user = auth('api')->user();
        $uid = $user['id'];
        $result = Reserve::query()->where(['uid' => $uid, 'aid' => $aid, 'status' => Reserve::RESERVE_BOOKED])->pluck('cid')->toArray();
        return new ReserveResource($result);

    }

}
