<?php

namespace App\Models\Activity;

use App\Models\Carte;
use App\Models\Configure;
use App\Models\Undertake;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Activity extends Model
{
    /*
     * status_show 活动状态
     */

    protected $table = 'activity'; // 活动会务表
    protected $fillable = ['uid', 'cover_image', 'title', 'activity_time',
        'apply_end_time', 'content', 'type', 'shelves_status', 'status','images',
        'longitude','latitude','address_title','address_name','visits', 'activity_end_time'];

    protected $datetimes = ['activity_time','apply_end_time'];

    protected $appends = [
        'status_show', 'status_type', 'type_show', 'undertake_select', 'list_display'
    ];

    const BACKSTAGE_USER = 1; // 后台用户

    const STATUS_PASSED = 1; // 通过
    const STATUS_UNDER_REVIEW = 2; // 审核中
    const STATUS_NOT_PASS = 3; // 未通过
    const STATUS_DELETED = 99; // 已删除

    // 是否结束活动
    const SHELVES_STATUS_ONE = 1; // 正常
    const SHELVES_STATUS_TWO = 2; // 活动已取消

    // 是否推荐
    const RECOMMEND_STATUS_ONE = 1; // 推荐
    const RECOMMEND_STATUS_TWO = 2; // 不推荐

    // 活动类型
    const TYPE_ONE = 1; // 活动
    const TYPE_TWO = 2; // 会务

    // 验证状态
    const CHECK_STATUS_ONE = 1;
    const CHECK_STATUS_TWO = 2;
    const CHECK_STATUS_THREE = 3;
    const CHECK_STATUS_FOUR = 4;
    const CHECK_STATUS_FIVE = 5;
    const CHECK_STATUS_SIX = 6;
    const CHECK_STATUS_SEVEN = 7;
    const CHECK_STATUS_EIGHT = 8;

    // 我的活动状态
    const CONDITION_ONE = 1;
    const CONDITION_TWO = 2;
    const CONDITION_THREE = 3;
    const CONDITION_FOUR = 4;
    const CONDITION_FIVE = 5;

    // 预约报名条件
    const CONDITION_TYPE_ONE = 1;
    const CONDITION_TYPE_TWO = 2;
    const CONDITION_TYPE_THREE = 3;
    const CONDITION_TYPE_FOUR = 4;
    const CONDITION_TYPE_FIVE = 5;
    const CONDITION_TYPE_SIX = 6;

    // 星期几
    const WEEK_ONE = 1;
    const WEEK_TWO = 2;
    const WEEK_THREE = 3;
    const WEEK_FOUR = 4;
    const WEEK_FIVE = 5;
    const WEEK_SIX = 6;
    const WEEK_SEVEN = 0;

    // 发现页活动状态
    const FIND_STATUS_ONE = 1;
    const FIND_STATUS_TWO = 2;
    const FIND_STATUS_THREE = 3;

    // 获取系统电话
    public function getBackstagesPhone() {
        $configureModel = new Configure();
        return $configureModel->getConfigure('CONTACT_NUMBER');
    }

    // 获取系统公司名
    public function getBackstagesCompanyName() {
        $configureModel = new Configure();
        return $configureModel->getConfigure('COMPANY_NAME');
    }

    // 获取推荐状态
    public function getRecommendStatus ($type='') {
        $data = [
            self::RECOMMEND_STATUS_ONE => '已推荐',
            self::RECOMMEND_STATUS_TWO => '未推荐'
        ];
        return $data[$type] ?? $data;
    }

    public function getStatus ($type='') {
        $data = [
            self::STATUS_PASSED => '已通过',
            self::STATUS_UNDER_REVIEW => '审核中',
            self::STATUS_NOT_PASS => '未通过',
            self::STATUS_DELETED => '已删除'
        ];
        return $data[$type] ?? $data;
    }

    public function getShelvesStatus ($type='') {
        $data = [
            self::SHELVES_STATUS_ONE => '正常',
            self::SHELVES_STATUS_TWO => '活动已取消'
        ];
        return $data[$type] ?? $data;
    }

    public function getFindStatus ($type='') {
        $data = [
            self::FIND_STATUS_ONE => '进行中',
            self::FIND_STATUS_TWO => '未开始',
            self::FIND_STATUS_THREE => '已结束'
        ];
        return $data[$type] ?? $data;
    }

    public function getWeekDay ($type='') {
        $data = [
            self::WEEK_ONE => '周一',
            self::WEEK_TWO => '周二',
            self::WEEK_THREE => '周三',
            self::WEEK_FOUR => '周四',
            self::WEEK_FIVE => '周五',
            self::WEEK_SIX => '周六',
            self::WEEK_SEVEN => '周日'
        ];
        return $data[$type] ?? $data;
    }

    public function getConditionType ($type='') {
        $data = [
            self::CONDITION_TYPE_ONE => '正常报名',
            self::CONDITION_TYPE_TWO => '已报名',
            self::CONDITION_TYPE_THREE => '人数已满',
            self::CONDITION_TYPE_FOUR => '报名未开始',
            self::CONDITION_TYPE_FIVE => '报名停止',
            self::CONDITION_TYPE_SIX => '活动已取消'
        ];
        return $data[$type] ?? $data;
    }


    public function getConditionStatus ($type='') {
        $data = [
            self::CONDITION_ONE => '报名中',
            self::CONDITION_TWO => '报名截止',
            self::CONDITION_THREE => '活动中',
            self::CONDITION_FOUR => '已结束',
            self::CONDITION_FIVE => '活动已取消',
        ];
        return $data[$type] ?? $data;
    }

    public function getCheckStatus ($type='') {
        $data = [
            self::CHECK_STATUS_ONE => '正常',
            self::CHECK_STATUS_TWO => '请登录',
            self::CHECK_STATUS_THREE => '您没有权限创建此项内容',
            self::CHECK_STATUS_FOUR => '该条记录不是你本人发布，无法修改',
            self::CHECK_STATUS_FIVE => '活动时间不可在今天之前',
            self::CHECK_STATUS_SIX => '报名结束时间不可在报名开始时间之前',
            self::CHECK_STATUS_SEVEN => '活动时间不可在报名截止时间之前',
            self::CHECK_STATUS_EIGHT => '报名截止时间不可在今天之前'
        ];
        return $data[$type] ?? $data;
    }

    // 组装推荐数组
    public function getRecommendData($row) {
        $data = [];
        $data['id'] = $row['id'];
        $data['recommend'] = $row['recommend'];
        $data['actionUrl'] = route('admin.activity.check-recommend');
        $data['redirectUrl'] = '';
        return $data;
    }

    public function getType($type = '') {
        $data = [
            self::TYPE_ONE => '活动',
            self::TYPE_TWO => '会务'
        ];
        return $data[$type] ?? $data;
    }

    // 活动分类
    public function getTypeShowAttribute(){
        $name = self::getType($this->type);
        return ($name && !is_array($name)) ? $name : '未知';
    }

    public function filterSearch($query,$search) {
        return $query->where('title','like',"%$search%");
    }

    public function filterType($query,$type) {
        return $query->where('type',$type);
    }

    public function filterMonth($query) {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        return $query->whereBetween('activity_time', [$startOfMonth,$endOfMonth]);
    }

    public function filterRecommend($query) {
        return $query->where('recommend',self::RECOMMEND_STATUS_ONE);
    }


    // 活动状态展示
    public function getStatusShowAttribute(){
        if ($this->shelves_status == self::SHELVES_STATUS_TWO) {
            return self::getShelvesStatus($this->shelves_status);
        }
        $activity_time = Carbon::parse($this->activity_time); // 活动时间
        $apply_end_time = Carbon::parse($this->apply_end_time); // 活动截止时间
        $today = Carbon::now();
        if ($today->lt($apply_end_time)) {
            return self::getConditionStatus(self::CONDITION_ONE);
        }
        // 活动当天
        if ($activity_time->isCurrentDay()) {
            return self::getConditionStatus(self::CONDITION_THREE);
        }
        if ($today->between($apply_end_time, $activity_time)) {
            return self::getConditionStatus(self::CONDITION_TWO);
        }
        if ($today->gt($activity_time)) {
            return self::getConditionStatus(self::CONDITION_FOUR);
        }
    }

    // 活动状态
    public function getStatusTypeAttribute(){
        if ($this->shelves_status == self::SHELVES_STATUS_TWO) {
            return self::CONDITION_FIVE;
        }
        $activity_time = Carbon::parse($this->activity_time); // 活动时间
        $apply_end_time = Carbon::parse($this->apply_end_time); // 活动截止时间
        $today = Carbon::now();
        if ($today->lt($apply_end_time)) {
            return self::CONDITION_ONE;
        }
        // 活动当天
        if ($activity_time->isCurrentDay()) {
            return self::CONDITION_THREE;
        }
        if ($today->between($apply_end_time, $activity_time)) {
            return self::CONDITION_TWO;
        }
        if ($today->gt($activity_time)) {
            return self::CONDITION_FOUR;
        }

    }

    public function getListDisplayAttribute() {
        $aid = $this->attributes['id'];
        $speList = Specification::query()->where('aid', $aid)->get();
        if ($speList->isNotEmpty()) {
            $num = 0;
            $isFree = false;
            foreach ($speList as $key => $value) {
                if ($value->price > 0) {
                    $num += $value->remainder;
                } else {
                    $isFree = true;
                    break;
                }
            }
            if ($isFree) {
                return '限时免费';
            } else {
                return "限{$num}人";
            }
        }
        return '';

    }


    public function setCoverImageAttribute($cover_image)
    {
        if (Str::startsWith($cover_image, ['http://', 'https://'])) {
            $this->attributes['cover_image'] = $cover_image;
            return true;
        }
        $new_image = '/storage/'.$cover_image;
        $this->attributes['cover_image'] = $new_image;
    }

    public function getCoverImageAttribute($cover_image)
    {
        if (Str::startsWith($cover_image, ['http://', 'https://'])) {
            return $cover_image;
        }
        $cover_image = config('app.url').$cover_image;
        return $cover_image;
    }

    public function setImagesAttribute($images)
    {
        if (is_array($images)) {
            $newImages = [];
            foreach ($images as $image){
                // 如果 image 字段本身就已经是完整的 url 就直接返回
                if (Str::startsWith($image, ['http://', 'https://'])) {
                    $newImages[] = $image;
                }else{
                    $newImages[] = Storage::disk('public')->url($image);
                }
            }
            $this->attributes['images'] = json_encode($newImages);
            return $this->attributes['images'];
        }
    }

    public function getImagesAttribute($images)
    {
        $newImages = [];
        $oldImages = json_decode($images, true);
        if(!empty($oldImages)){
            foreach ($oldImages as $image){
                // 如果 image 字段本身就已经是完整的 url 就直接返回
                if (Str::startsWith($image, ['http://', 'https://'])) {
                    $newImages[] = $image;
                }else{
                    $newImages[] = Storage::disk('public')->url($image);
                }
            }
        }

        return $newImages;
    }

    public function chengeSpe ($aid, $uid) {
        Specification::where('aid', $aid)->update(['uid' => $uid]);
    }

    public function chengeAgenda ($aid, $uid) {
        Agenda::where('aid', $aid)->update(['uid' => $uid]);
    }

    // 验证该数组是否实际添加
    public function checkAdminData($data) {
        if (empty($data)) {
            return false;
        }
        $i = 0;
        foreach ($data as $value) {
            if ($value['_remove_'] == 0) {
                $i++;
                break;
            }
        }
        if ($i == 0) {
            return false;
        }
        return true;
    }

    public function getActivityTimeAttribute($time)
    {
        return substr($time, 0, 16);
    }

    public function getActivityEndTimeAttribute($time)
    {
        if ($time) {
            $activity = $this->activity_time;
            if (substr($activity, 0, 10) == substr($time, 0, 10)) {
                return substr($time, 11, 5);
            }
            return substr($time, 0, 16);
        }

    }

    public function getApplyEndTimeAttribute($time)
    {
        return substr($time, 0, 16);
    }

    public function specification(){
        return $this->hasMany(Specification::class,'aid');
    }


    public function apply(){
        return $this->hasMany(ActivityApply::class,'aid')->where(['status' => ActivityApply::STATUS_COMPLETED])->orderBy('created_at','asc');
    }

    public function carte() {
        return $this->hasOne(Carte::class,'uid','uid')
            ->select('id','uid','name','company_name','phone','position','avatar');
    }

    public function agenda() {
        return $this->hasMany(Agenda::class,'aid');
    }

    public function tricks() {
        return $this->hasOne(Tricks::class,'aid');
    }

    public function undertake() {
        return $this->hasMany(Undertake::class,'aid');
    }

    public function getUndertakeSelectAttribute(){}

}
