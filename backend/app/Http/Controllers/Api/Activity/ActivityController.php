<?php

namespace App\Http\Controllers\Api\Activity;

use App\Http\Requests\ActivityRequest;
use App\Http\Resources\Activity\ActivityResource;
use App\Models\Activity\Activity;
use App\Models\Activity\ActivityApply;
use App\Models\Activity\Agenda;
use App\Models\Activity\Specification;
use App\Models\Association;
use App\Models\CompanyCard;
use App\Models\CompanyCardRole;
use App\Models\Undertake;
use App\Models\User\UserBalance;
use App\Models\User\UserBalanceLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{

    public function checkData($id) {
        if (empty($id)) {
            abort(404,'获取数据失败，不存在此条信息');
        }
        $activityModel = new Activity();
        $res = $activityModel->where('id',$id)->select('id','uid')->first();
        if (empty($res)) {
            abort(404,'获取数据失败，不存在此条信息');
        }

        if ($res->uid != Auth::id()) {
            abort(404,'您不是该条信息发布者，无法修改');
        }
        return true;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(ActivityRequest $request)
    {
        $requestData = $request->all();
        $result =  app('Libraries\Creators\ActivityCreator')->create($requestData);
        return new ActivityResource($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activity\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function update(ActivityRequest $request, Activity $activity)
    {
        $requestData = $request->all();
        $this->checkData($requestData['id']);
        $result =  app('Libraries\Creators\ActivityCreator')->update($requestData);
        return new ActivityResource($result);
    }

    public function checkDetailId(Request $request) {
        if (!$request->get('id')) {
            abort(404,'获取内容失败');
        }
        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activity\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function show(Activity $activity,Request $request)
    {
        $this->checkDetailId($request);
        $id = $request->input('id');
        $result = $activity->where('id',$id)->first();
        if (!empty($result)) {
            $result->speArr = $this->getSpeArr($id);
            $result->undertakeArr = $this->getUndertakeArr($id);
            if ($result->type == $activity::TYPE_TWO) {
                $result->agendaArr = $this->getAgendaArr($id);
            }
        } else {
            $result = collect();
        }
        return new ActivityResource($result);
    }

    public function getSpeArr($id) {
        $speModel = new Specification();
        $res = $speModel->where('aid',$id)->select('id')->get();
        $newData = [];
        if (!empty($res)) {
            foreach ($res as $value) {
                $newData[] = $value->id;
            }
        }
        return $newData;
    }

    public function getAgendaArr($id) {
        $agendaModel = new Agenda();
        $res = $agendaModel->where('aid',$id)->select('id')->get();
        $newData = [];
        if (!empty($res)) {
            foreach ($res as $value) {
                $newData[] = $value->id;
            }
        }
        return $newData;
    }

    public function getUndertakeArr($id) {
        $undertakeModel = new Undertake();
        $res = $undertakeModel->where('aid',$id)->select('id', 'cid')->get();
        $newData = [];
        if (!empty($res)) {
            foreach ($res as $value) {
                $newData[] = $value->cid;
            }
        }
        return $newData;
    }

    public function bigDetail(Request $request) {
        $this->checkDetailId($request);
        $id = $request->input('id');
        $activity = new Activity();
        // 目前不做限制浏览量加1
        $activity->where('id',$id)->increment('visits');
        $where['status'] = $activity::STATUS_PASSED;
        $where['shelves_status'] = $activity::SHELVES_STATUS_ONE;
        $where['id'] = $id;
        $result = $activity->with(['specification', 'carte', 'agenda', 'tricks', 'undertake', 'apply'=>function ($query) {
            $query->with(['carte']);
        }])
            ->where($where)
            ->first();
        if (empty($result)) {
            abort(404,'信息不存在');
        }
        $result->minPrice = $this->searchMin($result->specification->toArray());
        if ($result->minPrice === false) {
            abort(404,'数据错误');
        }
        $result->maxPrice = $this->searchMax($result->specification->toArray());
        if ($result->maxPrice === false) {
            abort(404,'数据错误');
        }
        $result->countStint = $this->stintCount($result->specification->toArray());
        if ($result->countStint === false) {
            abort(404,'数据错误');
        }
        $requestUser = '';
        if (auth('api')->check()) {
            // 用户已经登录了...
            $requestUser  = auth('api')->user();
        }
        $result->requset_user = $requestUser;
        return new ActivityResource($result);
    }

    // 求规格的最小值
    public function searchMin($arr, $field = 'price')
    {
        if(!is_array($arr) || !$field){
            return false;
        }
        $temp = array();
        foreach ($arr as $key=>$val) {
            $temp[] = $val[$field];
        }
        return min($temp);
    }

    public function searchMax($arr, $field = 'price')
    {
        if(!is_array($arr) || !$field){
            return false;
        }
        $temp = array();
        foreach ($arr as $key=>$val) {
            $temp[] = $val[$field];
        }
        return max($temp);
    }

    // 求规格的总名额
    public function stintCount($arr, $field = 'stint')
    {
        if(!is_array($arr) || !$field){
            return false;
        }
        $x = 0;
        foreach ($arr as $key=>$val) {
            $temp[] = $val[$field];
            $x += $val[$field];
        }
        return $x;
    }

    public function getDayOfWeek($currentTime) {
        $result = $currentTime->dayOfWeek;
        $activityModel = new Activity();
        $realDay = $activityModel->getWeekDay($result);
        return $realDay;
    }

    public function getFormat($time) {
        $carbon = new Carbon();
        $currentTime = $carbon::parse($time);
        if ($currentTime->isCurrentYear()) {
            $value = $currentTime->format('m/d');
        } else {
            $value = $currentTime->format('Y/m/d');
        }
        return $value;
    }



    public function getAllList(Activity $activity, Request $request) {
        $where['status'] = $activity::STATUS_PASSED;
        $where['shelves_status'] = $activity::SHELVES_STATUS_ONE;
        $field = ['id','uid','type','cover_image','title','activity_time','apply_end_time','address_title','shelves_status'];
        /**
         * @var $query Builder
         */
        $query = $activity->where($where);
        // 是否在协会主页
        if($request->exists('aid')) {
            $companyIds = CompanyCardRole::query()->where(['aid' => $request->get('aid'), 'is_company' => CompanyCardRole::IS_COMPANY_TRUE])->pluck('company_id')->toArray();
            if ($companyIds) {
                $userIds = CompanyCard::query()->whereIn('id', $companyIds)->pluck('uid')->toArray();
                $query->whereIn('uid', $userIds);
            }
        }
        if ($search = $request->input('search','')) {
            $activity->filterSearch($query,$search);
        }
        if ($type = $request->input('type','')) {
            $activity->filterType($query,$type);
        }
        if ($is_month = $request->input('is_month','')) {
            $activity->filterMonth($query);
        }
        if ($recommend = $request->input('recommend','')) {
            $activity->filterRecommend($query);
            $today = Carbon::today()->toDateTimeString();
            $query->where('activity_time', '>', $today);
        }
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
        $lists = $query->with(['apply'=>function($query) {
                    $query->select('id', 'aid', 'uid', 'status')->with('carte');
                }])
                ->select($field)
                ->latest()
                ->paginate(10);
        return new ActivityResource($lists);
    }

    /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys   要排序的键字段
     * @param string $sort  排序类型  SORT_ASC     SORT_DESC
     * @return array 排序后的数组
     */
    public function arraySort($array, $keys, $sort = SORT_DESC) {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }

    public function myList() {
        $activity = new Activity();
        $user_id = Auth::id();
        $field = ['id','uid', 'cover_image', 'title', 'apply_end_time','activity_time', 'type', 'shelves_status', 'status','visits'];
        $status_normal = ActivityApply::STATUS_COMPLETED;
        $lists = $activity->select($field)
                ->withCount(['apply as applyNum' => function ($query) use ($status_normal) {
                    $query->where('status',$status_normal);
                }])
                ->where(['status'=>$activity::STATUS_PASSED, 'uid' => $user_id])
                ->latest()
                ->paginate(10);
        return new ActivityResource($lists);
    }

    public function joinList() {
        $apply = new ActivityApply();
        $user_id = Auth::id();
        $lists = $apply->with(['activity'])
            ->where(function ($query) {
                $query->whereHas('activity');
            })
            ->where('uid',$user_id)
            ->latest()
            ->paginate(10);
        return new ActivityResource($lists);
    }

    public function getApplyStatus(Request $request) {
        $aid = $request->input('aid');
        if (empty($aid)) {
            return new ActivityResource(collect());
        }
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $result = ActivityApply::where(['status' => ActivityApply::STATUS_COMPLETED, 'aid' => $aid, 'uid' => $user->id])->first();
            return new ActivityResource($result);
        } else {
            return new ActivityResource(collect());
        }
    }

    // 报名名单
    public function applyList(Request $request) {
        $id = $request->input('id');
        if (empty($id)) {
            abort(404,'页面数据不存在');
        }
        $detail = Activity::with(['apply' => function ($query) {
                $query->with(['carte']);
            }])
            ->where('id', $id)
            ->select('id', 'type','visits')
            ->first();
        if (empty($detail)) {
            abort(404,'页面数据不存在');
        }
        $user = '';
        if (auth('api')->check()) {
            // 用户已经登录了...
            $user  = auth('api')->user();
        }
        return new ActivityResource(compact('detail','user'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activity\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function destroy(Activity $activity, Request $request)
    {
        $this->checkDetailId($request);
        // 目前只有草稿可删除
        $id = $request->get('id');
        $result = $activity->where('id',$id)->first();
        if ($result->edit_type != 1) {
            abort(403,'目前只针对草稿可删除');
        }
        $activity->where('id',$id)->delete();
        return $activity;
    }


    public function changeShelves(Activity $activity, Request $request)
    {
        $this->checkDetailId($request);
        // 目前只有草稿可删除
        $id = $request->get('id');
        $result = $activity->where('id',$id)->first();
        if ($result->shelves_status != $activity::SHELVES_STATUS_ONE) {
            abort(403,'该活动状态异常，不可取消');
        }
        if ($result->uid != Auth::id()) {
            abort(403,'你不是该活动发布者，无法取消活动!');
        }
        if ($result->status_type != $activity::CONDITION_ONE) {
            abort(403,'该活动时间已过，我发取消');
        }
        // 查询该活动下的报名人员
        $applyModel = new ActivityApply();
        $applyList = $applyModel::where(['aid' => $result->id])->get();
        if (!$applyList->isEmpty()) {
            foreach ($applyList as $value) {
                if ($value->status == $applyModel::STATUS_COMPLETED && $value->pay_status == $applyModel::PAY_STATUS_PAID && $value->refund_status == $applyModel::REFUND_STATUS_REFUNDABLE) {
                    if ($value->price > 0) {
                        $this->applyRefund($value);
                    }
                }
                $applyModel->where('id', $value->id)->update(['status' => $applyModel::STATUS_CANCEL]);
            }
        }
        $result->update(['shelves_status' => $activity::SHELVES_STATUS_TWO]);
        return new ActivityResource(collect());
    }

    // 已报名用户退款
    public function applyRefund ($detail) {
        $orderModel = new ActivityApply();
        if (empty($detail)) {
            return false;
        }
        if ($detail->status != $orderModel::STATUS_COMPLETED || $detail->pay_status != $orderModel::PAY_STATUS_PAID || $detail->refund_status != $orderModel::REFUND_STATUS_REFUNDABLE) {
            return false;
        }
        switch ($detail->pay_type) {
            case $orderModel::PAY_TYPE_WECHAT:
                // 生成退款订单号
                $refundNo = $orderModel::getAvailableRefundNo();
                $refundMoney = bcmul($detail->price,100);
                try{
                    $a = app('wechat_pay')->refund([
                        'out_trade_no' => $detail->order_no,
                        'total_fee' => $refundMoney,
                        'refund_fee' => $refundMoney,
                        'out_refund_no' => $refundNo,
                        'notify_url' => ngrok_url('payment.wechat.refund_notify'),
                    ]);
                    $detail->update([
                        'refund_no' => $refundNo,
                        'refund_status' => ActivityApply::REFUND_STATUS_SUCCESS,
                    ]);
                    return true;
                } catch (\Exception $exception){
                    return false;
                }
                break;
            case $orderModel::PAY_TYPE_BALANCE:
                $this->balanceRefundNotify($detail);
                break;
            default:
                return false;
                break;
        }
    }


    public function balanceRefundNotify ($detail) {
        $orderModel = new ActivityApply();
        if ($detail->status != $orderModel::STATUS_COMPLETED || $detail->pay_status != $orderModel::PAY_STATUS_PAID || $detail->refund_status != $orderModel::REFUND_STATUS_REFUNDABLE) {
            return false;
        }
        if ($detail->pay_type != $orderModel::PAY_TYPE_BALANCE) {
            return false;
        }
        if (!($detail->price >0)) {
            return false;
        }
        try{
            DB::beginTransaction();
            $price = $detail->price;
            UserBalance::where('user_id', $detail->uid)->increment('money', $price);
            $userBalanceLog = new UserBalanceLog();
            $userBalanceLog::addLog($detail->uid, $userBalanceLog::LOG_TYPE_INCOME, $userBalanceLog::TYPE_ACTIVITY_REFUND, $price, '', $detail->aid, $detail->id);
            Specification::where('id', $detail->sid)->increment('remainder');
            $detail->update([
                'status' => $orderModel::STATUS_CANCEL,
                'refund_status' => $orderModel::REFUND_STATUS_SUCCESS,
                'refund_at' => Carbon::now()
            ]);
            DB::commit();
            return true;
        }catch (\Exception $exception){
            DB::rollBack();
            return false;
        }



    }
}
