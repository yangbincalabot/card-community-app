<?php
namespace Libraries\Creators;


use App\Models\Association;
use App\Models\CompanyRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyRoleCreator
{
    public function addOrUpdate($data){
        $id = $data['id'] ?? 0;
        $aid = $data['aid'];
        $association = Association::query()->find($aid);
        $newData['uid'] = $association->user_id === 0 ? 0 : Auth::id();
        $newData['name'] = $data['name'];
        if (isset($data['fee'])){
            $newData['fee'] = $data['fee'];
        }
        $secMsg = $data['name'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        if($id > 0){
            $res = CompanyRole::query()->where(['id' => $id])->firstOrFail();
            $res->update($newData);
//            $res->update($newData);
//            $res = CompanyRole::query()->updateOrCreate(['id' => $id, 'aid' => $aid], $newData);
        }else{
            $res = CompanyRole::query()->create(array_merge($newData, ['aid' => $aid]));
        }

        if (empty($id) && !empty($res)) {
            $res->sort = $res->id;
            $res->save();
        }
        return $res;
    }

}
