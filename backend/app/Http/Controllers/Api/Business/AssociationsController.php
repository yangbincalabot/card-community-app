<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Requests\Business\AssociationsRequest;
use App\Http\Resources\baseResource;
use App\Http\Resources\CardIndexResource;
use App\Http\Resources\CommonResource;
use App\Models\CompanyCardRole;
use App\Models\CompanyRole;
use App\Services\AssociationsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

// 商家协会管理
class AssociationsController extends Controller
{
    protected $service;

    public function __construct(AssociationsService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request){
        $associations = $this->service->getAssociations($request->user());
        return new CommonResource(compact('associations'));
    }

    public function create(AssociationsRequest $request){
        $this->service->createAssociations($request->user(), $request->all());
    }

    public function show(Request $request){
        $association = $this->service->showOwnAssociation($request->user(), $request->get('id'));
        return new CommonResource(compact('association'));
    }

    public function selectAssociation(Request $request){
        $list = $this->service->selectAssociation($request->get('id'));
        return new CommonResource(compact('list'));
    }

    public function update(AssociationsRequest $request){
        $this->service->updateAssociation($request->user(), $request->get('id'), $request->all());
    }

    public function delete(Request $request){
        $this->service->deleteAssociation($request->get('id'));
    }

    // 申请记录
    public function application(Request $request){
        $applications = $this->service->applicationList($request->get('aid'));
        return new CommonResource(compact('applications'));
    }

    // 下级审核记录
    public function subAudit(Request $request){
        $list = $this->service->subAudit($request->get('pid'));
        return new CommonResource($list);
    }

    // 下级审核
    public function subAuditVerify(Request $request){
        $aid = $request->get('aid');
        $status = $request->get('status');
        $this->service->subAuditVerify($aid, $status);
    }

    // 审核入会
    public function verify(Request $request){
        $aid = $request->get('aid');
        $id = $request->get('id');
        $status = $request->get('status');
        $this->service->verify($aid, $id, $status);
    }


    // 协会广场(会员单位)
    public function companies(Request  $request) {
        $aid = $request->get('aid');

        $roleCompanyList = CompanyRole::query()->where('aid', $aid)->with(['society' => function($query) use ($request){
            $this->bindSocietySearch($query, $request);
        }])->whereHas('society', function($query) use ($request){
            $this->bindSocietySearch($query, $request);
        })
            ->orderBy('sort', 'asc')
            ->orderBy('updated_at', 'desc')
            ->get();

        return new CardIndexResource(compact('roleCompanyList'));
    }

    private function bindSocietySearch($query, $request){
        if ($search = $request->input('search','')) {
            $like = '%'.$search.'%';
            $query->where('company_name', 'like', $like);
        }else{
            $query->where('company_name', '<>', '');
        }
    }

    // 协会信息
    public function info(Request  $request){
        $aid = $request->get('aid');
        $association = $this->service->info($aid);
        return new baseResource(compact('association'));
    }
}
