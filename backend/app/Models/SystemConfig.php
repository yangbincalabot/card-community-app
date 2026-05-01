<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    // 获取活动年龄分组
    public function getActivityGroup($group = []){
        $arr = [
            [
                'id'=>1,
                'start'=>1,
                'end'=>1.5,
                'sex' => 1,
                'type' => 1,
                'name'=>'1岁小龄男子组',
            ],
            [
                'id'=>2,
                'start'=>1,
                'end'=>1.5,
                'sex' => 2,
                'type' => 1,
                'name'=>'1岁小龄女子组',
            ],
            [
                'id'=>3,
                'start'=>1.5,
                'end'=>2,
                'sex' => 1,
                'type' => 1,
                'name'=>'1岁大龄男子组',
            ],
            [
                'id'=>4,
                'start'=>1.5,
                'end'=>2,
                'sex' => 2,
                'type' => 1,
                'name'=>'1岁大龄女子组',
            ],
            [
                'id'=>5,
                'start'=>1,
                'end'=>2,
                'sex' => 1,
                'type' => 1,
                'name'=>'1岁男子组',
            ],
            [
                'id'=>6,
                'start'=>1,
                'end'=>2,
                'sex' => 2,
                'type' => 1,
                'name'=>'1岁女子组',
            ],
            [
                'id'=>7,
                'start'=>2,
                'end'=>2.5,
                'sex' => 1,
                'type' => 1,
                'name'=>'2岁小龄男子组',
            ],
            [
                'id'=>8,
                'start'=>2,
                'end'=>2.5,
                'sex' => 2,
                'type' => 1,
                'name'=>'2岁小龄女子组',
            ],
            [
                'id'=>9,
                'start'=>2.5,
                'end'=>3,
                'sex' => 1,
                'type' => 1,
                'name'=>'2岁大龄男子组',
            ],
            [
                'id'=>10,
                'start'=>2.5,
                'end'=>3,
                'sex' => 2,
                'type' => 1,
                'name'=>'2岁大龄女子组',
            ],
            [
                'id'=>11,
                'start'=>2,
                'end'=>3,
                'sex' => 1,
                'type' => 1,
                'name'=>'2岁男子组',
            ],
            [
                'id'=>12,
                'start'=>2,
                'end'=>3,
                'sex' => 2,
                'type' => 1,
                'name'=>'2岁女子组',
            ],
            [
                'id'=>13,
                'start'=>3,
                'end'=>3.5,
                'sex' => 1,
                'type' => 1,
                'name'=>'3岁小龄男子组',
            ],
            [
                'id'=>14,
                'start'=>3,
                'end'=>3.5,
                'sex' => 2,
                'type' => 1,
                'name'=>'3岁小龄女子组',
            ],
            [
                'id'=>15,
                'start'=>3.5,
                'end'=>4,
                'sex' => 1,
                'type' => 1,
                'name'=>'3岁大龄男子组',
            ],
            [
                'id'=>16,
                'start'=>3.5,
                'end'=>4,
                'sex' => 2,
                'type' => 1,
                'name'=>'3岁大龄女子组',
            ],
            [
                'id'=>17,
                'start'=>3,
                'end'=>4,
                'sex' => 1,
                'type' => 1,
                'name'=>'3岁男子组',
            ],
            [
                'id'=>18,
                'start'=>3,
                'end'=>4,
                'sex' => 2,
                'type' => 1,
                'name'=>'3岁女子组',
            ],
            [
                'id'=>19,
                'start'=>4,
                'end'=>4.5,
                'sex' => 1,
                'type' => 1,
                'name'=>'4岁小龄男子组',
            ],
            [
                'id'=>20,
                'start'=>4,
                'end'=>4.5,
                'sex' => 2,
                'type' => 1,
                'name'=>'4岁小龄女子组',
            ],
            [
                'id'=>21,
                'start'=>4.5,
                'end'=>5,
                'sex' => 1,
                'type' => 1,
                'name'=>'4岁大龄男子组',
            ],
            [
                'id'=>22,
                'start'=>4.5,
                'end'=>5,
                'sex' => 2,
                'type' => 1,
                'name'=>'4岁大龄女子组',
            ],
            [
                'id'=>23,
                'start'=>4,
                'end'=>5,
                'sex' => 1,
                'type' => 1,
                'name'=>'4岁男子组',
            ],
            [
                'id'=>24,
                'start'=>4,
                'end'=>5,
                'sex' => 2,
                'type' => 1,
                'name'=>'4岁女子组',
            ],
            [
                'id'=>25,
                'start'=>5,
                'end'=>5.5,
                'sex' => 1,
                'type' => 1,
                'name'=>'5岁小龄男子组',
            ],
            [
                'id'=>26,
                'start'=>5,
                'end'=>5.5,
                'sex' => 2,
                'type' => 1,
                'name'=>'5岁小龄女子组',
            ],
            [
                'id'=>27,
                'start'=>5.5,
                'end'=>6,
                'sex' => 1,
                'type' => 1,
                'name'=>'5岁大龄男子组',
            ],
            [
                'id'=>28,
                'start'=>5.5,
                'end'=>6,
                'sex' => 2,
                'type' => 1,
                'name'=>'5岁大龄女子组',
            ],
            [
                'id'=>29,
                'start'=>5,
                'end'=>6,
                'sex' => 1,
                'type' => 1,
                'name'=>'5岁男子组',
            ],
            [
                'id'=>30,
                'start'=>5,
                'end'=>6,
                'sex' => 2,
                'type' => 1,
                'name'=>'5岁女子组',
            ],
            [
                'id'=>31,
                'start'=>0,
                'end'=>0,
                'sex' => 3,
                'type' => 2,
                'name'=>'公开组',
            ],
        ];
        if(!empty($group)){
            $newData = [];
            $idArr = array_keys($group);
            foreach ($arr as $item){
                if(in_array($item['id'],$idArr)){
                    $item['value'] = $group[$item['id']];
                    $newData[] = $item;
                }
            }
            return $newData;
        }
        return $arr;
    }

    public function getGroupInfo($group_id) {
        $groupArr = self::getActivityGroup();
        $currentArr = [];
        foreach ($groupArr as $value) {
            if ($value['id'] == $group_id) {
                $currentArr = $value;
                break;
            }
        }
        return $currentArr;
    }

    public function getActivityText () {
//        $str = "1、我是注意事项，别再等来日方长，因为时间不会等你，趁着趁时光正好，想旅游就去。生命来来往往，来日并不方长。
//        愿此生我们都能拼过命、尽过兴，不负岁月。愿，所有的爱，都还来得及；愿，所有的等待，都不被辜负。<br/>";
//        $str .= "&nbsp;&nbsp;&nbsp;2、头，要抬得起来，低得下去。抬头看天是一种方向，低头看路是一种清醒；抬头做事是一种勇气，低头做人是一种底气；抬头微笑是一种心态，低头看花是一种智慧。";
        $str = '报名采取先来后到顺序，额满即止（请仔细核对报名信息是否正确再提交，一旦提交资料无法更改）。';
        return $str;
    }
}
