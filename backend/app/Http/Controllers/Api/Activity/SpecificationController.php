<?php

namespace App\Http\Controllers\Api\Activity;

use App\Http\Requests\SpecificationRequest;
use App\Http\Resources\Activity\SpecificationResource;
use App\Models\Activity\Specification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/*
 *  活动会务规格类
 */
class SpecificationController extends Controller
{
    public function create(SpecificationRequest $request) {
        $speModel = new Specification();
        $requestData = $request->all();
        $user = Auth::user();
        $createData['uid'] = $user['id'];
//        $createData['aid'] = $requestData['aid'];
        $createData['title'] = $requestData['title'];
        $createData['stint'] = $requestData['stint'];
        $createData['remainder'] = $requestData['stint'];
        $createData['price'] = $requestData['price'];
        // 检测输入文本是否合法
        $secMsg = $createData['title'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        $result = $speModel->create($createData);
        return new SpecificationResource($result);
    }

    public function update(SpecificationRequest $request) {
        $speModel = new Specification();
        $requestData = $request->all();
        $id = $requestData['id'];
        $this->checkData($id);
//        $createData['aid'] = $requestData['aid'];
        $createData['title'] = $requestData['title'];
        $createData['stint'] = $requestData['stint'];
        $createData['remainder'] = $requestData['stint'];
        $createData['price'] = $requestData['price'];
        // 检测输入文本是否合法
        $secMsg = $createData['title'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        $speModel->where('id',$id)->update($createData);
        return new SpecificationResource($speModel);
    }

    public function getList(Specification $specification,Request $request) {
        $idArr = $request->input('idArr');
        if (!is_array($idArr)) {
            abort(403,'错的数据类型');
        }
        $result = $specification->whereIn('id',$idArr)->get();
        return new SpecificationResource($result);
    }

    public function show(Specification $specification,Request $request) {
        $id = $request->get('id');
        $this->checkDetailId($id);
        $result = $specification->where('id',$id)->first();
        return new SpecificationResource($result);
    }

    public function delete(Specification $specification,Request $request) {
        $id = $request->get('id');
        $this->checkData($id);
        $specification->where('id',$id)->delete();
        return new SpecificationResource($specification);
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
        $speModel = new Specification();
        $res = $speModel->where('id',$id)->select('id','uid')->first();
        if (empty($res)) {
            abort(404,'获取数据失败，不存在此条信息');
        }
        if ($res->uid != Auth::id()) {
            abort(403,'您不是该条信息发布者，无法操作');
        }
        return true;
    }
}
