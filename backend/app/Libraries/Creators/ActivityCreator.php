<?php
namespace Libraries\Creators;


use App\Models\Activity\Activity;
use App\Models\Activity\Agenda;
use App\Models\Activity\Specification;
use App\Models\Carte;
use App\Models\Configure;
use App\Models\Undertake;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityCreator
{
    public function create($data){
        DB::beginTransaction();
        $activityModel = new Activity();
        $configure = new Configure();
        $user = Auth::user();
        $createData['uid'] = $user['id'];
        $createData['cover_image'] = $data['cover_image'];
        $createData['type'] = $data['type'];
        $createData['title'] = $data['title'];
        $createData['activity_time'] = $data['activity_time'];
        $data['activity_end_time'] && $createData['activity_end_time'] = $data['activity_end_time'];
        $createData['apply_end_time'] = $data['apply_end_time'] ? $data['apply_end_time']: $data['activity_time'];
        $createData['content'] = $data['content'];
        $createData['longitude'] = $data['longitude'];
        $createData['latitude'] = $data['latitude'];
        $createData['address_title'] = $data['address_title'];
        $createData['address_name'] = $data['address_name'];
        $createData['images'] = $data['images'];
        $reviewStatus = $configure->getConfigure('ACTIVITY_VERIFY');
        // 检测输入文本是否合法
        $secMsg = $data['title'].$data['content'].$data['address_title'].$data['address_name'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        if ($reviewStatus == $configure::ACTIVITY_VERIFY_YES) $createData['status'] = $activityModel::STATUS_UNDER_REVIEW;
//        $result = $activityModel->create($createData);
        $result = Activity::query()->create($createData);
        $this->addSpe($result->id,$data);
        if ($data['type'] == $activityModel::TYPE_TWO) {
            $this->addAgenda($result->id,$data);
        }
        $this->addUndertake($result->id,$data);
        DB::commit();
        return $result;
    }

    public function addUndertake($aid,$data) {
        $undertakeArr = $data['undertakeArr'];
        DB::beginTransaction();
        try{
            $undertakeModel = new Undertake();
            $carteModel = new Carte();
            // 先清空旧的承办单位数据
            $undertakeModel->where('aid', $aid)->delete();
            if (empty($undertakeArr)) {
                return true;
            }
            foreach ($undertakeArr as $v) {
                $oldRes = $carteModel->where('id',$v)->select('id', 'name', 'company_name')->first();
                if (empty($oldRes)) {
                    continue;
                }
                $createData['aid'] = $aid;
                $createData['cid'] = $v;
                $createData['name'] = $oldRes->name;
                $createData['company'] = $oldRes->company_name;
                $undertakeModel->create($createData);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, '添加承办人出错');
        }
        return true;
    }

    public function addAgenda($aid,$data) {
        $agendaArr = $data['agendaArr'];
        if (empty($agendaArr)) {
            abort(403,'请最少填写一项议程');
        }

        if (count($agendaArr) > 10) {
            abort(403,'填写最大数为10');
        }
        DB::beginTransaction();
        try{
            $agendaModel = new Agenda();
            $oldIdArr = $agendaModel->where('aid', $aid)->pluck('id')->toArray();
            $x = 0; // 标识符，以防没有添加议程
            foreach ($agendaArr as $v) {
                if (in_array($v, $oldIdArr)) {
                    $sk = array_search($v, $oldIdArr);
                    unset($oldIdArr[$sk]);
                }
                $oldRes = $agendaModel->where('id',$v)->select('id')->first();
                if (!empty($oldRes)) {
                    $x++;
                    $agendaModel->where('id',$v)->update(['aid'=>$aid]);
                }
            }
            if ($x == 0) {
                abort(403,'请最少填写一项议程');
            }
            $oldIdArr = array_values($oldIdArr);
            $agendaModel->whereIn('id', $oldIdArr)->delete(); // 删除旧的数据
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, '添加议程出错');
        }
        return true;
    }


    public function addSpe($aid,$data) {
        $speArr = $data['speArr'];
        if (empty($speArr)) {
            abort(403,'请最少填写一项规格');
        }

        if (count($speArr) > 10) {
            abort(403,'规格最大数为10');
        }
        $speModel = new Specification();
        DB::beginTransaction();
        try{
            $oldIdArr = $speModel->where('aid', $aid)->pluck('id')->toArray();
            $x = 0; // 标识符，以防没有添加规格
            foreach ($speArr as $key => $v) {
                if (in_array($v, $oldIdArr)) {
                    $sk = array_search($v, $oldIdArr);
                    unset($oldIdArr[$sk]);
                }
                $oldRes = $speModel->where('id',$v)->select('id')->first();
                if (!empty($oldRes)) {
                    $x++;
                    $speModel->where('id',$v)->update(['aid'=>$aid]);
                }
            }
            if ($x == 0) {
                abort(403,'请最少填写一项规格');
            }
            $oldIdArr = array_values($oldIdArr);
            $speModel->whereIn('id', $oldIdArr)->delete(); // 删除旧的数据
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, '添加规格出错');
        }
        return true;
    }

    public function update($data){
        DB::beginTransaction();
        $activityModel = new Activity();
        $configure = new Configure();
        $id = $data['id'];
        $createData['cover_image'] = $data['cover_image'];
        $createData['type'] = $data['type'];
        $createData['title'] = $data['title'];
        $createData['activity_time'] = $data['activity_time'];
        $data['activity_end_time'] && $createData['activity_end_time'] = $data['activity_end_time'];
        $createData['apply_end_time'] = $data['apply_end_time'] ? $data['apply_end_time']: $data['activity_time'];
        $createData['content'] = $data['content'];
        $createData['longitude'] = $data['longitude'];
        $createData['latitude'] = $data['latitude'];
        $createData['address_title'] = $data['address_title'];
        $createData['address_name'] = $data['address_name'];
        $secMsg = $data['title'].$data['content'].$data['address_title'].$data['address_name'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
       // $createData['images'] = $activityModel->setImagesAttribute($data['images']);
        $reviewStatus = $configure->getConfigure('ACTIVITY_VERIFY');
        if ($reviewStatus == $configure::ACTIVITY_VERIFY_YES) $createData['status'] = $activityModel::STATUS_UNDER_REVIEW;
//        $activityModel->where('id',$id)->update($createData);
        Activity::query()->where('id', $id)->update($createData);
        $this->addSpe($id,$data);
        if ($data['type'] == $activityModel::TYPE_TWO) {
            $this->addAgenda($id,$data);
        }
        $this->addUndertake($id,$data);
        DB::commit();
        return $activityModel;
    }
}
