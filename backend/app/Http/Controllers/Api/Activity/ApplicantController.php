<?php

namespace App\Http\Controllers\Api\Activity;

use App\Http\Resources\Activity\ApplicantResource;
use App\Models\Activity\Activity;
use App\Models\Activity\Applicant;
use App\Models\SystemConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Medz\IdentityCard\China\Identity;

class ApplicantController extends Controller
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

    public function checkIdCard ($id_card) {
        $peopleIdentity = new Identity($id_card);
        $peopleRegion = $peopleIdentity->legal();
        return $peopleRegion;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $applicantModel = new Applicant();
        $data = $request->all();
        $user_id = Auth::id();
        $createData = [];
        $region_type = $data['region_type'];
        $id_card = $data['identity_number'];
        if ($region_type == 1) {
            $check_card = $this->checkIdCard($id_card);
            if (!$check_card) {
                abort(403,'请正确输入身份证号');
            }
            $createData['birthday'] = $this->get_birthday($id_card);
        } else {
            if (!$data['birthday']) {
                abort(403,'请选择出生日期');
            }
            $createData['birthday'] = $data['birthday'];
        }
        DB::beginTransaction();
        $createData['user_id'] = $user_id;
        $createData['region_type'] = $region_type;
        $createData['name'] = $data['name'];
        $createData['identity_number'] = $id_card;
        $createData['phone'] = $data['phone'];
        $createData['sex'] = $data['sex'];
        $result = $applicantModel->create($createData);
        DB::commit();
        return new ApplicantResource($result);
    }

    public function getList() {
        $user_id = Auth::id();
        $applicant = new Applicant();
        $whereData['status'] = 1;
        $whereData['user_id'] = $user_id;
        $result = $applicant->where($whereData)->orderBy('created_at','desc')->get();
        if (!$result->isEmpty()) {
            return new ApplicantResource($result);
        }
    }

    public function checkDetailId(Request $request) {
        $bool = false;
        if ($request->get('id')) {
            $bool = true;
        }
        return $bool;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activity\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function show(Applicant $applicant,Request $request)
    {
        if (!$this->checkDetailId($request)) {
            abort(403,'获取内容失败');
        }
        $id = $request->get('id');
        $result = $applicant->where('id',$id)->first();
        return new ApplicantResource($result);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Activity\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function edit(Applicant $applicant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activity\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        if (!$id) {
            abort('403','更新失败');
        }
        if ($data['uid'] != Auth::id()) {
            abort('403','该条信息不是您发布，您不可以更新');
        }
        $createData = [];
        $region_type = $data['region_type'];
        $id_card = $data['identity_number'];
        if ($region_type == 1) {
            $check_card = $this->checkIdCard($id_card);
            if (!$check_card) {
                abort(403,'请正确输入身份证号');
            }
            $createData['birthday'] = $this->get_birthday($id_card);
        } else {
            if (!$data['birthday']) {
                abort(403,'请选择出生日期');
            }
            $createData['birthday'] = $data['birthday'];
        }
        DB::beginTransaction();
        $applicantModel = new Applicant();
        $createData['region_type'] = $data['region_type'];
        $createData['name'] = $data['name'];
        $createData['identity_number'] = $id_card;
        $createData['phone'] = $data['phone'];
        $createData['sex'] = $data['sex'];
        $applicantModel->where('id',$id)->update($createData);
        DB::commit();
        return new ApplicantResource($applicantModel);
    }



    public function checkChoose(Request $request) {
        if (!$this->checkDetailId($request)) {
            abort(403,'获取内容失败');
        }
        $id = $request->get('id');
        $group_id =  $request->get('group_id');
        $activity_id =  $request->get('activity_id');
        if (!$group_id || !$activity_id) {
            abort(403,'信息不全,获取内容失败');
        }
        $applicantModel = new Applicant();
        $applicantResult = $applicantModel->where('id',$id)->first();
        $birthday = $applicantResult->birthday;
        if (!$birthday) {
            abort(403,'生日信息不全，请更改报名人信息后，重新提交！');
        }
        $configModel = new SystemConfig();
        $currentArr =  $configModel->getGroupInfo($group_id);
        if ($currentArr['type'] == $applicantModel::GROUP_TYPE_TWO) {
            // 若为公开组，则直接验证通过
            return new ApplicantResource(collect());
        }
        if ($currentArr['sex'] != $applicantResult->sex) {
            // 性别不符合该组
            $status = $applicantModel::CHECK_STATUS_TWO;
            $msg = $applicantModel->getCheckStatus($status);
            abort(403,$msg);
        }
        $activityModel = new Activity();
        $activity_time = $activityModel->where('id',$activity_id)->value('activity_time');
        $carbon = new Carbon();
        $minTime = $this->get_current_date($currentArr['end'],$activity_time);
        $maxTime = $this->get_current_date($currentArr['start'],$activity_time);
        $startTime = $carbon->parse($minTime);
        $endTime = $carbon->parse($maxTime);
        $check = $carbon->parse($birthday)->between($startTime, $endTime);
        if (!$check) {
            $status = $applicantModel::CHECK_STATUS_THREE;
            $msg = $applicantModel->getCheckStatus($status);
            abort(403,$msg);
        }
        return new ApplicantResource(collect());
    }

    public function get_current_date($number,$time){
        $carbon = new Carbon();
        if (ceil($number) == $number) {
            $current = $carbon::parse($time)->modify("-$number years")->format('Y-m-d');
        } else {
            $number = ceil($number);
            $current = $carbon::parse($time)->modify("-$number years")->modify("+6 months")->format('Y-m-d');
        }
        return $current;
    }

    public function get_birthday($idcard) {
        if(empty($idcard)) return null;
        $peopleIdentity = new Identity($idcard);
        $peopleRegion = $peopleIdentity->birthday();
        return $peopleRegion;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activity\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Applicant $applicant,Request $request)
    {
        if (!$this->checkDetailId($request)) {
            abort(403,'获取内容失败');
        }
        $id = $request->get('id');
        $result = $applicant->where('id',$id)->first();
        if ($result->user_id != Auth::id()) {
            abort(403,'不是本人，无法删除');
        }
        $applicant->where('id',$id)->delete();
        return  '';
    }
}
