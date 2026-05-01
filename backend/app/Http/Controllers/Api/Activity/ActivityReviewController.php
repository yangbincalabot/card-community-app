<?php

namespace App\Http\Controllers\Api\Activity;

use App\Http\Resources\Activity\ActivityReviewResource;
use App\Models\Activity\ActivityReview;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ActivityReviewController extends Controller
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

    public function carteValidate($request) {
        $this->validate($request, [
            'cover_image' => ['required'],
            'title' => ['required','max:220'],
            'content' => ['required']
        ],[
            'cover_image.required' => '请上传封面图片',
            'title.required' => '请填写回顾标题',
            'title.max' => '标题过长',
            'content.required' => '详情内容不能为空'
        ]);
    }

    public function checkUser() {
        $user = Auth::user();
        $userModel = new User();
        $reviewModel = new ActivityReview();
        $status = $reviewModel::CHECK_STATUS_ONE;
        if (!$user['id']) {
            $status = $reviewModel::CHECK_STATUS_TWO;
        }
        if ($user['type'] != $userModel::USER_TYPE_FOUR) {
            $status = $reviewModel::CHECK_STATUS_THREE;
        }
        $msg = $reviewModel->getCheckStatus($status);
        return ['status'=>$status,'msg'=>$msg];
    }

    public function checkDetailId(Request $request) {
        $bool = false;
        if ($request->get('id')) {
            $bool = true;
        }
        return $bool;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->carteValidate($request);
        $checkData = $this->checkUser();
        $reviewModel = new ActivityReview();
        if ($checkData['status'] != $reviewModel::CHECK_STATUS_ONE) {
            abort(403,$checkData['msg']);
        }
        $requestData = $request->all();
        $user = Auth::user();
        $createData['user_id'] = $user['id'];
        $createData['cover_image'] = $requestData['cover_image'];
        $createData['title'] = $requestData['title'];
        $createData['content'] = $requestData['content'];
        $createData['type'] = $requestData['type'];
        isset($requestData['status']) && $createData['status'] = $requestData['status'];
        // 检测输入文本是否合法
        $secMsg = $createData['title'].$createData['content'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        $result = $reviewModel->create($createData);
        return new ActivityReviewResource($result);
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
     * @param  \App\Models\Activity\ActivityReview  $activityReview
     * @return \Illuminate\Http\Response
     */
    public function show(ActivityReview $activityReview ,Request $request)
    {
       if (!$this->checkDetailId($request)) {
           abort(403,'获取内容失败');
       }
       $id = $request->get('id');
       $result = $activityReview->where('id',$id)->first();
       $storeModel = new Store();
       $result->store_name = $storeModel->where('user_id',$result->user_id)->value('name');
       return new ActivityReviewResource($result);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Activity\ActivityReview  $activityReview
     * @return \Illuminate\Http\Response
     */
    public function edit(ActivityReview $activityReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activity\ActivityReview  $activityReview
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ActivityReview $activityReview)
    {
        $this->carteValidate($request);
        $checkData = $this->checkUser();
        $reviewModel = new ActivityReview();
        if ($checkData['status'] != $reviewModel::CHECK_STATUS_ONE) {
            abort(403,$checkData['msg']);
        }
        $requestData = $request->all();
        $id = $requestData['id'];
        $createData['cover_image'] = $requestData['cover_image'];
        $createData['title'] = $requestData['title'];
        $createData['content'] = $requestData['content'];
        $createData['type'] = $requestData['type'];
        // 检测输入文本是否合法
        $secMsg = $createData['title'].$createData['content'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        $reviewModel->where('id',$id)->update($createData);
        return new ActivityReviewResource($reviewModel);
    }

    public function reviewList(Request $request)
    {
        $limit =  $request->get('limit');
        $reviewModel = new ActivityReview();
        $whereData['type'] = $reviewModel::TYPE_TWO;
        $whereData['status'] = $reviewModel::STATUS_TWO;
        $query = $reviewModel->where($whereData);
        if ($limit) {
            $query->limit($limit);
            $result = $query->orderBy('created_at','desc')->get();
        } else {
            $result = $query->orderBy('created_at','desc')->latest()->paginate(10);
        }

        if (!$result->isEmpty()) {
            return new ActivityReviewResource($result);
        }
    }

    public function myList() {
        $reviewModel = new ActivityReview();
        $user_id = Auth::id();
        $result = $reviewModel->where('user_id',$user_id)->orderBy('created_at','desc')->get();
        if (!$result->isEmpty()) {
            return new ActivityReviewResource($result);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activity\ActivityReview  $activityReview
     * @return \Illuminate\Http\Response
     */
    public function destroy(ActivityReview $activityReview,Request $request)
    {
        if (!$this->checkDetailId($request)) {
            abort(403,'获取内容失败');
        }
        $id = $request->get('id');
        $result = $activityReview->where('id',$id)->first();
        if ($result->user_id != Auth::id()) {
            abort(403,'不是本人，无法删除');
        }
        $activityReview->where('id',$id)->delete();
        return new ActivityReviewResource($activityReview);
    }
}
