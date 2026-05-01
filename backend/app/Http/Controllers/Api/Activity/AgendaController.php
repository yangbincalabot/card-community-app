<?php

namespace App\Http\Controllers\Api\Activity;

use App\Http\Requests\AgendaRequest;
use App\Http\Resources\Activity\AgendaResource;
use App\Models\Activity\Agenda;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


/*
 * 会务议程类
 */
class AgendaController extends Controller
{
    public function create(AgendaRequest $request) {
        $agendaModel = new Agenda();
        $requestData = $request->all();
        $user = Auth::user();
        $createData['uid'] = $user['id'];
        $createData['presenter'] = $requestData['presenter'];
        $createData['pid'] = $requestData['presenter_id'];
        $createData['title'] = $requestData['title'];
        $createData['start_time'] = $requestData['start_time'];
        $createData['end_time'] = $requestData['end_time'];
        // 检测输入文本是否合法
        $secMsg = $createData['presenter'].$createData['title'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        $result = $agendaModel->create($createData);
        return new AgendaResource($result);
    }

    public function update(AgendaRequest $request) {
        $agendaModel = new Agenda();
        $requestData = $request->all();
        $id = $requestData['id'];
        $this->checkData($id);
        $createData['presenter'] = $requestData['presenter'];
        $createData['pid'] = $requestData['presenter_id'];
        $createData['title'] = $requestData['title'];
        $createData['start_time'] = $requestData['start_time'];
        $createData['end_time'] = $requestData['end_time'];
        // 检测输入文本是否合法
        $secMsg = $createData['presenter'].$createData['title'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        $agendaModel->where('id',$id)->update($createData);
        return new AgendaResource($agendaModel);
    }

    public function show(Agenda $agenda,Request $request) {
        $id = $request->get('id');
        $this->checkDetailId($id);
        $result = $agenda->where('id',$id)->first();
        return new AgendaResource($result);
    }

    public function getList(Agenda $agenda,Request $request) {
        $idArr = $request->input('idArr');
        if (!is_array($idArr)) {
            abort(403,'错的数据类型');
        }
        $result = $agenda->whereIn('id',$idArr)->get();
        return new AgendaResource($result);
    }

    public function delete(Agenda $agenda,Request $request) {
        $id = $request->get('id');
        $this->checkData($id);
        $agenda->where('id',$id)->delete();
        return new AgendaResource($agenda);
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
        $agendaModel = new Agenda();
        $res = $agendaModel->where('id',$id)->select('id','uid')->first();
        if (empty($res)) {
            abort(404,'获取数据失败，不存在此条信息');
        }

        if ($res->uid != Auth::id()) {
            abort(403,'您不是该条信息发布者，无法操作');
        }
        return true;
    }

}
