<?php

namespace App\Http\Controllers\Api\Activity;

use App\Http\Requests\ApplyRequest;
use App\Http\Resources\Activity\ActivityApplyResource;
use App\Jobs\CloseActivityApplyOrder;
use App\Models\Activity\Activity;
use App\Models\Activity\ActivityApply;
use App\Http\Controllers\Controller;
use App\Models\Activity\Specification;
use App\Models\Carte;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityApplyController extends Controller
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


    // 支付金额大于0 （非免费）创建报名
    public function create(ApplyRequest $request)
    {
        $data = $request->all();
        // 验证报名信息
        $this->checkApply($data['aid']);
        $this->checkSpeRemainder($data['aid'], $data['sid']);
        DB::beginTransaction();
        $applyModel = new ActivityApply();
        $createData['uid'] = Auth::id();
        $createData['aid'] = $data['aid'];
        $createData['sid'] = $data['sid'];
        $createData['name'] = $data['name'];
        $createData['phone'] = $data['phone'];
        $createData['company_name'] = $data['company_name'];
        $createData['price'] = $data['price'];
        $order_no = createOrderNo();
        $createData['order_no'] = $order_no;
        $createData['payment_no'] = $order_no;
        $createData['pay_status'] = $applyModel::PAY_STATUS_PENDING;
        $result = $applyModel->create($createData);
        DB::commit();
        if ($result) {
            // 这里我们直接使用 dispatch 函数
            dispatch(new CloseActivityApplyOrder($result, config('app.order_ttl')));
            return new ActivityApplyResource($result);
        }
    }

    // 免费活动报名
    public function freeApply(ApplyRequest $request)
    {
        $data = $request->all();
        // 验证报名信息
        $this->checkApply($data['aid']);
        $this->checkSpeRemainder($data['aid'], $data['sid']);
        DB::beginTransaction();
        $applyModel = new ActivityApply();
        $createData['uid'] = Auth::id();
        $createData['aid'] = $data['aid'];
        $createData['sid'] = $data['sid'];
        $createData['name'] = $data['name'];
        $createData['phone'] = $data['phone'];
        $createData['company_name'] = $data['company_name'];
        $createData['price'] = $data['price'];
        $order_no = createOrderNo();
        $createData['order_no'] = $order_no;
        $createData['payment_no'] = $order_no;
        $createData['pay_status'] = $applyModel::PAY_STATUS_NO_NEED;
        $createData['refund_status'] = $applyModel::REFUND_STATUS_NOT;
        $createData['status'] = $applyModel::STATUS_COMPLETED;
        // 规格剩余人数减1
        Specification::where(['id' => $data['sid'], 'aid' => $data['aid']])->decrement('remainder');
        $result = $applyModel->create($createData);
        DB::commit();
        if ($result) {
            return new ActivityApplyResource($result);
        }
    }

    // 用户主动取消订单
    public function cancelOrder (Request $request) {
        $order_id = $request->input('id');
        if (empty($order_id)) {
            abort(404,'信息不存在，请退出后重试');
        }
        $applyModel = new ActivityApply();
        $result = $applyModel->where('id', $order_id)->first();
        if (empty($result)) {
            abort(404,'信息不存在，请退出后重试');
        }
        if ($result->uid != Auth::id()) {
            abort(403,'你不是该条订单创建者，无法取消');
        }
        if ($result->status == $applyModel::STATUS_COMPLETED || $result->pay_status == $applyModel::PAY_STATUS_PAID) {
            abort(403,'该订单已完成，无法取消');
        }
        $applyModel->where('id', $result->id)->update([
            'pay_status' => $applyModel::PAY_STATUS_TIMEOUT,
            'refund_status' => $applyModel::REFUND_STATUS_NOT,
            'status' => $applyModel::STATUS_CANCEL,
        ]);
        return new ActivityApplyResource(collect());
    }

    public function getBigDetail(Request $request) {
        $aid = $request->input('aid');
        $sid = $request->input('sid');
        if (!$aid || !$sid) {
            abort(404,'信息不存在，请退出后重试');
        }
        $carteResult = Carte::where('uid', Auth::id())->first();
        // 验证名片
        $this->checkCarte($carteResult);
        $sepResult = Specification::where('id',$sid)->first();
        // 验证规格
        $this->checkSpe($sepResult,$aid);
        $activityResult = Activity::where('id',$aid)->first();
        // 验证活动
        $this->checkActivity($activityResult);
        // 查看是否已报名
        $this->checkApply($aid);
        $newData = [];
        $newData['carte'] = $carteResult;
        $newData['spe'] = $sepResult;
        $newData['activity'] = $activityResult;
        return new ActivityApplyResource($newData);
    }

    // 验证该用户是否已报名
    public function checkApply($aid) {
        if (empty($aid)) {
            abort(404,'活动不存在');
        }
        $activityResult = Activity::where('id',$aid)->first();
        $this->checkActivity($activityResult);
        $applyModel = new ActivityApply();
        $where['uid'] = Auth::id();
        $where['aid'] = $aid;
        $where['status'] = $applyModel::STATUS_COMPLETED;
        $result = $applyModel->where($where)->first();
        if (!empty($result)) {
            abort(404,'您已报名该活动,不要重复报名');
        }
    }

    // 验证规格是否存在，及改规格报名人数是否已满
    public function checkSpeRemainder ($aid, $sid) {
        $res = Specification::where(['aid' => $aid, 'id' => $sid])->first();
        if (empty($res)) {
            abort(403, '报名错误，该活动规格为空');
        }

        if ($res->remainder == 0) {
            abort(403, '该活动规格人数已满，请选择其它规格进行报名');
        }
    }


    // 验证活动信息
    public function checkActivity ($activityResult) {
        if (empty($activityResult)) {
            abort(404,'活动不存在');
        }
        $apply_end_time = Carbon::parse($activityResult->apply_end_time); // 活动截止时间
        $today = Carbon::now();
        if ($today->gt($apply_end_time)) {
            abort(404,'该活动报名已截止');
        }
        if ($activityResult->shelves_status != Activity::SHELVES_STATUS_ONE) {
            abort(404,'该活动已下架');
        }
    }

    // 验证规格信息
    public function checkSpe ($sepResult, $aid) {
        if (empty($sepResult)) {
            abort(404,'规格信息不存在');
        }
        if ($sepResult->aid != $aid) {
            abort(404,'规格信息与该活动不符');
        }
    }

    // 验证名片信息
    public function checkCarte ($carteResult) {
        if (empty($carteResult)) {
            abort(404,'您还没有开通名片，请前往个人中心开通后再进行活动报名');
        }
        if (empty($carteResult->name) || empty($carteResult->company_name) || empty($carteResult->phone)) {
            abort(404,'您的名片信息不全，请补充完整后再进行活动报名');
        }
    }

    // 报名订单信息
    public function orderDetail(Request $request) {
        $id = $request->input('id', '');
        if (!$id) {
            abort(404,'报名信息不存在');
        }
        $result = ActivityApply::with(['activity','specification', 'carte'])->where('id',$id)->first();
        if (empty($result)) {
            abort(404,'报名信息不存在');
        }
        $result->is_refund = ActivityApply::getIsRefund($result);
        return new ActivityApplyResource($result);
    }


    public function myList() {


    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activity\ActivityApply  $activityApply
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {

    }





    public function applyRefund(Request $request,ActivityApply $activityApply){
        $id = $request->input('id');
        $user_id = $request->user()->id;
        $where['id'] = $id;
        $where['uid'] = $user_id;
        $where['status'] = $activityApply::STATUS_COMPLETED;
        $where['pay_status'] = $activityApply::PAY_STATUS_PAID;
        $info = $activityApply->where($where)->first();
        if(empty($info)){
            abort(403,'订单不存在');
        }

        // 判断订单是否已付款
        if ((!$info->paid_at)) {
            abort(403,'该订单未支付，不可退款');
        }

        // 判断订单退款状态是否正确
        if ($info->refund_status !== $activityApply::REFUND_STATUS_REFUNDABLE) {
            abort(403,'该订单目前不可退款');
        }

        // 判断订单退款状态是否正确
        if (!($info->price > 0)) {
            abort(403,'该订单不可退款');
        }

        // 将订单退款状态改为已申请退款
        $info->update([
            'refund_status' => $activityApply::REFUND_STATUS_PROCESSING,
        ]);
        return new ActivityApplyResource($info);
    }
}
