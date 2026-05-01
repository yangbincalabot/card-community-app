<?php

namespace App\Http\Controllers\Api\Activity;

use App\Http\Requests\TricksRequest;
use App\Http\Resources\Activity\TricksResource;
use App\Models\Activity\Tricks;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TricksController extends Controller
{
    public function create(TricksRequest $request) {
        $tricksModel = new Tricks();
        $requestData = $request->all();
        $uid = Auth::id();
        $createData['uid'] = $uid;
        $createData['aid'] = $requestData['aid'];
        $createData['content'] = $requestData['content'];
        $createData['images'] = $requestData['images'];
        // 先查询该活动下有没有创建过花絮
        $oldResult = $tricksModel->where(['uid' => $uid, 'aid' => $requestData['aid']])->first();
        if (!empty($oldResult)) {
            $updateData['content'] = $requestData['content'];
            $updateData['images'] = $requestData['images'];
            $tricksModel->where('id', $oldResult->id)->update($updateData);
            return new TricksResource($tricksModel);
        }
        $result = $tricksModel->create($createData);
        return new TricksResource($result);
    }

    public function update(TricksRequest $request) {
        $tricksModel = new Tricks();
        $requestData = $request->all();
        $id = $requestData['id'];
        $this->checkData($id);
        $createData['aid'] = $requestData['aid'];
        $createData['content'] = $requestData['content'];
        $createData['images'] = $tricksModel->setImagesAttribute($requestData['images']);
        $tricksModel->where('id',$id)->update($createData);
        return new TricksResource($tricksModel);
    }

    public function show(Tricks $tricks,Request $request) {
        $aid = $request->get('aid');
        if (empty($aid)) {
            abort(404,'活动信息不存在');
        }
        $uid = Auth::id();
        $result = $tricks->where(['uid' => $uid, 'aid' => $aid])->first();
        if (empty($result)) {
            $result = collect();
        }
        return new TricksResource($result);
    }

    public function delete(Tricks $tricks,Request $request) {
        $id = $request->get('id');
        $this->checkData($id);
        $result = $tricks->where('id',$id)->delete();
        return new TricksResource($result);
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
        $tricksModel = new Tricks();
        $res = $tricksModel->where('id',$id)->select('id','uid')->first();
        if (empty($res)) {
            abort(404,'获取数据失败，不存在此条信息');
        }

        if ($res->uid != Auth::id()) {
            abort(403,'您不是该条信息发布者，无法操作');
        }
        return true;
    }
}
