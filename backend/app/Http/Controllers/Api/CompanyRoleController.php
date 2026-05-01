<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CompanyRoleRequest;
use App\Http\Resources\baseResource;
use App\Models\Association;
use App\Models\CompanyCard;
use App\Models\CompanyCardRole;
use App\Models\CompanyRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CompanyRoleController extends Controller
{
    // 角色列表(个人中心)
    public function roleList(Request $request) {
        // 协会aid
        $aid = $request->get('aid');
        $association = Association::query()->find($aid);
        $user = $request->user();
        if ($association instanceof Association && ($association->user_id == $user->id || $user->is_admin == User::IS_ADMIN_TRUE)){
            $list = CompanyRole::query()->where('aid', $aid)->orderBy('sort', 'asc')->orderBy('updated_at', 'desc')->get();
        }else{
            $list = [];
        }

        return new baseResource($list);
    }

    // 角色公司列表
    public function roleCompany(Request $request) {
        $id = $request->get('id','');
        $aid = $request->get('aid');
        if (empty($id) || empty($aid)) {
            abort(404, '未知信息');
        }
        /**
         * @var $roleInfo CompanyRole
         */
        $roleInfo = CompanyRole::query()->where('id', $id)->where('aid', $aid)->first();
        if (empty($roleInfo)) {
            abort(404, '不存在的公司规则');
        }

        // 企业的
        $list = $roleInfo->companys()->with(['user' => function ($query) {
            $query->with(['carte' => function($query) {
                $query->select('uid', 'name', 'phone', 'avatar');
            }]);
        }])->whereHas('user',function ($query) {
            $query->where('enterprise_at', '>=', Carbon::now());
        })->wherePivot('role_id', $id)->orderBy('company_card_roles.role_sort', 'asc')->orderBy('updated_at', 'desc')->get();

        // 个人的
        $personal = $roleInfo->cartes()->with('user')->whereHas('user', function ($query) {
            $query->where('enterprise_at', '>=', Carbon::now());
        })->wherePivot('role_id', $id)->orderBy('company_card_roles.role_sort', 'asc')->orderBy('updated_at', 'desc')->get();
        $list = $list->concat($personal);

        // 重新设置排序， pivot.role_sort  asc
        $list = $list->sortBy(function ($item) {
            return $item->pivot->role_sort;
        });
        $list = $list->values();
        return new baseResource(compact('roleInfo', 'list'));
    }

    // 未选中的公司
    public function noSelectdCompany(Request $request) {
        $keyword = $request->input('keyword', '');
        $aid = $request->input('aid', 0);
//        $sql = CompanyCard::query()->with(['user' => function ($query) {
//            $query->with(['carte' => function($query) {
//                $query->select('uid', 'name', 'phone', 'avatar');
//            }]);
//        }])->whereHas('user',function ($query) {
//            $query->where('enterprise_at', '>=', Carbon::now());
//        })->where('role_id', 0)->where('company_name', '<>', '')
//            ->where('status',CompanyCard::TYPE_NORMAL);

        $selectCompanyIds = CompanyCardRole::query()->where('aid', $aid)->pluck('company_id')->toArray();
        $sql = CompanyCard::query()->with(['user' => function ($query) {
            $query->with(['carte' => function($query) {
                $query->select('uid', 'name', 'phone', 'avatar');
            }]);
        }])->whereHas('user', function ($query){
            $query->where('enterprise_at', '>=', Carbon::now());
        })->whereNotIn('id', $selectCompanyIds)->where('status', CompanyCard::TYPE_NORMAL);

        if ($keyword) {
            $sql->where('company_name', 'like','%'.$keyword.'%');
        }else{
            $sql->where('company_name', '<>', '');
        }
        $list = $sql->orderBy('updated_at', 'desc')->paginate(10);
        return new baseResource($list);
    }


    // 协会角色调整排序
    public function roleAdjustSort(Request $request) {
        $type = $request->input('type', ''); // 1:上移 || 下移 2：置顶
        $id = $request->input('id', '');
        $to_id = $request->input('to_id', '');
        $aid = $request->input('aid', 0);
        if (empty($type)) {
            abort(403, '数据为空');
        } else if($type == 1 && (empty($id) || empty($to_id))) {
            abort(403, '更新失败');
        } else if($type == 2 &&  empty($id)) {
            abort(403, '更新失败');
        }
        $info = CompanyRole::query()->where('id', $id)->first();
        if (empty($info)) {
            abort(403, '更新失败');
        }
        if ($type == 1) {
            DB::beginTransaction();
            try{
                $sort = $info->sort;
                $toInfo = CompanyRole::query()->where('id', $to_id)->first();
                if (empty($toInfo)) {
                    abort(403, '更新失败');
                }
                $to_sort = $toInfo->sort;
                $toInfo->sort = $sort;
                $toInfo->save();
                $info->sort = $to_sort;
                $info->save();
                DB::commit();
                return new baseResource(collect());
            }catch (\Exception $e){
                \Log::error($e->getMessage());
                DB::rollBack();
                abort(500, '更新失败');
            }
        } else {
            DB::beginTransaction();
            try{
                CompanyRole::query()->where('aid', $aid)->increment('sort');
                $info->sort = 1;
                $info->save();
                DB::commit();
                return new baseResource(collect());
            }catch (\Exception $e){
                \Log::error($e->getMessage());
                DB::rollBack();
                abort(500, '更新失败');
            }
        }


    }


    // 协会公司顺序调整
    public function companyAdjustSort(Request $request) {
        $type = $request->input('type', ''); // 1:上移 || 下移 2：置顶
        $role_id = $request->input('role_id', '');
        $id = $request->input('id', '');
        $to_id = $request->input('to_id', '');
        $aid = $request->get('aid');
        if (empty($type) || empty($role_id) || empty($aid)) {
            abort(403, '数据为空');
        } else if($type == 1 && (empty($id) || empty($to_id))) {
            abort(403, '更新失败');
        } else if($type == 2 && empty($id)) {
            abort(403, '更新失败');
        }
        $roleInfo = CompanyRole::query()->where('id', $role_id)->first();
        if (empty($roleInfo)) {
            abort(403, '协会角色不存在');
        }
        $info = CompanyCard::query()->where('id', $id)->first();
        if (empty($info)) {
            abort(403, '更新失败');
        }
        $provi = request()->get('provi');
        $to_provi = request()->get('to_provi');
        if (!is_array($provi) || !is_array($to_provi)) {
            abort(403, '参数错误');
        }
        if ($type == 1) {
            DB::beginTransaction();
            try{
                $sort = CompanyCardRole::query()->where('id', $provi['id'])->value('role_sort');
                $toInfo = CompanyCard::query()->where('id', $to_id)->first();
                if (empty($toInfo)) {
                    abort(403, '更新失败');
                }

                // 修改编辑逻辑

                $to_sort = CompanyCardRole::query()->where('id', $to_provi['id'])->value('role_sort');
                CompanyCardRole::query()->where('id', $to_provi['id'])->update(['role_sort' => $sort]);


                CompanyCardRole::query()->where('id', $provi['id'])->update(['role_sort' => $to_sort]);

                DB::commit();
                return new baseResource(collect());
            }catch (\Exception $e){
                \Log::error($e->getMessage());
                DB::rollBack();
                abort(500, '更新失败');
            }
        } else {
            DB::beginTransaction();
            try{
                // 已废除
                CompanyCard::query()->where('role_id', $role_id)->increment('role_sort');
                $info->role_sort = 1;
                $info->save();

                // 修改company_card_roles表
                $pivot = request()->get('pivot');
                if (is_array($pivot) && isset($pivot['id'])) {
                    CompanyCardRole::query()->where('aid', $aid)->increment('role_sort');
                    CompanyCardRole::query()->where('id', $pivot['id'])->update(['role_sort' => 1]);
                }

                DB::commit();
                return new baseResource(collect());
            }catch (\Exception $e){
                \Log::error($e->getMessage());
                DB::rollBack();
                abort(500, '更新失败');
            }
        }

    }

    // 添加/更新公司角色
    public function store(CompanyRoleRequest $request) {
        $data = $request->all();
        $result = app('Libraries\Creators\CompanyRoleCreator')->addOrUpdate($data);
        return new baseResource($result);
    }

    // 给某个公司添加某个角色
    public function addCompanyRole(Request $request) {
        $id = $request->input('id', '');
        $role_id = $request->input('role_id', '');
        $aid = $request->input('aid', 0);
        if (empty($id) || empty($role_id) || empty($aid)) {
            abort(403, '数据为空');
        }
        $roleInfo = CompanyRole::query()->where('id', $role_id)->first();
        if (empty($roleInfo)) {
            abort(403, '协会角色不存在');
        }
        $association = Association::query()->find($aid);
        if (empty($association)){
            abort(403, '协会不存在');
        }

        if ($association->status !== Association::STATUS_SUCCESS){
            abort(403, '协会未通过审核');
        }
        $info = CompanyCard::query()->where('id', $id)->first();
        if (empty($info)) {
            abort(403, '公司信息不存在');
        }
        // 查出该协会角色排序最小的一位
//        $minRoleSort = CompanyCard::query()->where('role_id', $role_id)->orderBy('role_sort', 'desc')->value('role_sort') ?? 0;
        $minRoleSort = CompanyCardRole::query()->where('role_id', $role_id)->where('aid', $aid)->orderBy('role_sort', 'desc')->value('role_sort') ?? 0;
        $minRoleSort ++;
        // 修改添加角色逻辑
        if (!CompanyCardRole::query()->where(['company_id' => $info->id, 'role_id' => $role_id])->exists()){
            CompanyCardRole::query()->create([
                'role_id' => $role_id,
                'role_sort' => $minRoleSort,
                'aid' => $aid,
                'company_id' => $info->id,
            ]);
        }
//        $info->role_id = $role_id;
//        $info->role_sort = $minRoleSort;
//        $info->save();
        return new baseResource(collect());
    }

    // 删除某个公司的协会角色
    public function delCompanyRole(Request $request) {
        $aid = $request->input('aid', 0);
        $pivot = $request->input('pivot');
        if (!is_array($pivot) || empty($aid)) {
            abort(403, '数据为空');
        }
        $companyCardRole = CompanyCardRole::query()->find($pivot['id']);
        if (!$companyCardRole || $companyCardRole->aid != $aid) {
            abort(403, '参数有误');
        }

        $companyCardRole->delete();
        return new baseResource(collect());
    }

}
