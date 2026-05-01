<?php

namespace App\Models;

use App\Models\Traits\ImagesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Carte extends Model
{
    use ImagesTrait;
    protected $table = 'carte'; // 个人名片表

//    protected $fillable = ['uid', 'cid', 'name', 'company_name', 'avatar',
//        'phone', 'wechat', 'email', 'introduction', 'industry_id','position',
//        'open', 'images','status', 'longitude','latitude','address_title',
//        'address_name','visits', 'province', 'city'];

    protected $guarded = ['id'];

    const OPEN_ONE = 1; // 公开
    const OPEN_TWO = 2; // 不公开

    protected $appends = ['original_company_name'];


    public function getAvatarAttribute($value){
        if (empty($value) || Str::startsWith($value, ['data:image'])) {
            return $value;
        }
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $this->attributes['avatar'];
        }
        return Storage::disk('public')->url($value);
    }

    // 如果关联则显示关联的公司名，否则显示名片编辑时填入的公司名，两者不影响
    public function getCompanyNameAttribute($company_name){
        if ($this->cid) {
//            $name = CompanyCard::where(['id' => $this->cid, 'status' => CompanyCard::TYPE_NORMAL])->value('company_name');
            $company_card = $this->company_card;
            $name = ($company_card && $company_card->status === CompanyCard::TYPE_NORMAL) ? $company_card->company_name : '';
            $company_name = $name ?: $company_name;
        }
        return $company_name;
    }

    // 原个人名片公司名称
    public function getOriginalCompanyNameAttribute(){
        if (!empty($this->attributes['company_name'])) {
            return $this->attributes['company_name'];
        }
        return '';
    }

    // 所属用户
    public function user(){
        return $this->belongsTo(User::class, 'uid');
    }

    // 关联公司
    public function company_card(){
        return $this->belongsTo(CompanyCard::class, 'cid');
    }

    // 所属行业
    public function industry(){
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    // 关联企业创建者
    public function company () {
        return $this->belongsTo(CompanyCard::class, 'uid', 'uid');
    }

    /**
     * 名片是否完善，因为名片编辑已验证手机号、姓名、公司名、行业、职务为必填字段，所以只要验证其它字段
     * @return bool|mixed
     */
    public function getPerfectAttribute(){
        $isPerfect = true;
//        $check_fields = ['avatar', 'wechat', 'email', 'introduction', 'address_title', 'cid', 'images'];
        $check_fields = ['avatar', 'name', 'company_name', 'phone', 'position', 'address_title'];
        foreach($check_fields as $field) {
            if (empty($this->{$field})){
                $isPerfect = false;
                break;
            }
        }
        return $isPerfect;
    }


    // 获取名片列表，并组装成数组
    public function getCarteAll ($uid = '') {
        $result = Carte::where('status', 1)->where('uid', '<>', 0)->where('cid', '<>', 0)->select('id', 'uid', 'name', 'phone','company_name')->orderBy('id','asc')->get();
        $data = [];
        if (!$result->isEmpty()) {
            foreach ($result  as $value) {
                $data[$value->uid] = $value->name.' ('.$value->phone.')';
            }
        }
        return $data[$uid] ?? $data;
    }

    // 企业录入时，组装名片数组
    public function getCarteData ($uid = '') {
        $result = Carte::doesntHave('company')->where('status', 1)->select('id', 'uid', 'name', 'phone','company_name')->orderBy('id','asc')->get();
        $data = [];
        if (!$result->isEmpty()) {
            foreach ($result  as $value) {
                $name = $value->name;
                if ($value->phone) {
                    $name .= ' ('.$value->phone.')';
                }
                if ($value->company_name) {
                    $name .= ' ('.$value->company_name.')';
                }
                $data[$value->uid] = $name;
            }
        }
        return $data[$uid] ?? $data;
    }


    // 解除公司绑定
    public static function unBind(User $user){
        $carte = $user->carte;
        if($carte && $carte->cid > 0){
            // 过期的解除绑定
            if($carte->company_card && $carte->company_card->uid == $user->id && !$user->companyCardStatus){
                $carte->cid = 0;
                $carte->save();
            }
            // 公司对应的用户不存在，解除绑定
            if(!$carte->company_card || !$carte->company_card->user){
                $carte->cid = 0;
                $carte->save();
            }
        }
    }

    public function carteDepartments(){
        return $this->hasMany(CarteDepartment::class, 'carte_id');
    }

}
