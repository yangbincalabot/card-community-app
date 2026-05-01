<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\BindDepartmentRequest;
use App\Http\Requests\DepartmentRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\CarteDepartment;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    use SoftDeletes;
    protected $service;
    public function __construct(DepartmentService $service)
    {
        $this->service = $service;
    }

    // 获取所有部门
    public function index(Request $request){
        $user = $request->user();
        $departments = $this->service->list($user->id);
        return new DepartmentResource(compact('departments'));
    }

    public function store(DepartmentRequest $request){
        $department = $this->service->store([
            'name' => $request->get('name'),
            'uid' => $request->user()->id,
        ]);
        return new DepartmentResource(compact('department'));
    }

    // 绑定部门
    public function bind(BindDepartmentRequest $request){
        $user = $request->user();
        $department = Department::query()->find($request->get('department_id'));
        if($user->id !== $department->uid){
            abort(403, '非法操作');
        }
        $id = $request->get('id');
        if($id > 0){
            // id > 0 为编辑
            $carteDepartment = CarteDepartment::query()->find($id);
            if($carteDepartment){
                $carteDepartment->department_id = $department->id;
                $carteDepartment->save();
                return new DepartmentResource(compact('carteDepartment'));
            }
        }
        $carteDepartment = CarteDepartment::addCarteDepartment($user->id, $department->id, $request->get('carte_id'));
        return new DepartmentResource(compact('carteDepartment'));
    }

    // 部门列表
    public function list(Request $request){
        $user = $request->user();
        $departments = $this->service->list($user->id, true);
        return new DepartmentResource(compact('departments'));
    }

    public function detail(Request $request){
        $department = Department::query()->find($request->get('id'));
        $user = $request->user();
        if($department->uid !== $user->id){
            abort(403, '非法操作');
        }
        return new DepartmentResource(compact('department'));
    }

    public function update(DepartmentUpdateRequest $request){
        $user = $request->user();
        $department = Department::query()->find($request->get('id'));
        if($user->id !== $department->uid){
            abort(403, '非法操作');
        }
        $department->name = $request->get('name');
        $department->save();
        return new DepartmentResource(compact('department'));
    }

    public function delete(Request $request){
        $department = Department::query()->findOrFail($request->get('id'));
        $user = $request->user();
        if($user->id !== $department->uid){
            abort(403, '非法操作');
        }
        DB::beginTransaction();
        try{
            CarteDepartment::query()->where('department_id', $department->id)->update(['department_id' => 0]);
            $department->delete();
            $ok = Response::HTTP_OK;
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            \Log::error($exception->getTraceAsString());
            abort(503, '删除失败');
        }
        return new DepartmentResource(compact('ok'));
    }

    public function bindOff(Request $request){
        $carteDepartment = '';
        $id = $request->get('id');
        $user = $request->user();
        $carteDepartment = CarteDepartment::query()->where(['id' => $id, 'uid' => $user->id])->first();
        return new DepartmentResource(compact('carteDepartment'));
    }
}
