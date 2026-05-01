<?php

namespace App\Admin\Extensions;
use App\Models\Activity\Activity;
use App\Models\Activity\Applicant;
use App\Models\Exports\ListExport;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Facades\Excel;

class ApplicantExporter extends AbstractExporter
{
    public function __construct()
    {
        parent::__construct();
    }

    public function export()
    {
        $data = $this->getData();
        $list = $this->setData($data);
        $export = new ListExport($list);
        $path = 'public/exports/';
        $filename =  date('Y').'/'.date('m').'/'.date('d').'/'.time().rand(111,999).'.xlsx';
        $realPath = $path.$filename;
        Excel::store($export, $realPath);
        $path = config('app.url').'/storage/exports/'.$filename;
        header("location:$path");
        exit;
    }

    public function setData($data)
    {
        $activityModel =  new Activity();
        $applicantModel = new Applicant();
        $group = $activityModel->getGroupType();
        $regionArr = $applicantModel->getRegionType();
        $sexArr = $applicantModel->getSex();
        $head = ['id','活动组别', '报名id', '活动id','紧急联系人','联系人手机号','户籍','姓名','性别','身份证号','生日','手机号','报名时间'];
        $realData = [];
        foreach ($data as $key => $value) {
            $new_value = [];
            // $activity = $value['activity'];
            $apply = $value['apply'];
            $applicant = $value['applicant'];
            $new_value[] = $value['id'];
            $new_value[] = $group[$value['group_id']];
            $new_value[] = $value['apply_id'];
            $new_value[] = $value['activity_id'];
            $new_value[] = $apply['urgent_contact'];
            $new_value[] = $this->transcoding($apply['phone']);
            // $new_value[] = $activity['title'];
            $new_value[] = $regionArr[$applicant['region_type']];
            $new_value[] = $applicant['name'];
            $new_value[] = $sexArr[$applicant['sex']];
            $new_value[] = $this->transcoding($applicant['identity_number']);
            $new_value[] = $applicant['birthday'];
            $new_value[] = $this->transcoding($applicant['phone']);
            $new_value[] = $applicant['created_at'];
            $realData[] = $new_value;
        }
        $startData[0] = $head;
        $endData = array_merge($startData,$realData);
        return $endData;
    }

    // 将身份证格式以及手机号转换为excel表格正确的格式
    public function transcoding($variable) {
        $variable = "=\"".$variable."\"";
        iconv("UTF-8", "GB2312", $variable);
        return $variable;
    }

}