<?php

namespace App\Models;

use App\Models\Traits\ImagesTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanyCard extends Model
{
    use ImagesTrait;
    protected $table = 'company_card'; // 企业名片表

    // 企业状态
    const TYPE_NORMAL = 1; // 正常
    const TYPE_EXPIRE = 2; // 过期
    const TYPE_DELETE = 99; // 删除

    protected $fillable = ['uid', 'company_name', 'logo', 'contact_number',
        'industry_id', 'introduction', 'website', 'images', 'status', 'longitude',
        'latitude', 'address_title', 'address_name', 'initial'];

    protected $appends = ['industry_name'];


    // 所属用户
    public function user(){
        return $this->belongsTo(User::class, 'uid');
    }

    public function carte(){
        return $this->hasMany(Carte::class, 'cid')->select('id', 'uid', 'cid', 'name', 'company_name', 'phone', 'position', 'avatar');
    }


    public function getLogoAttribute($logo){
        if (empty($logo) || Str::startsWith($logo, ['http://', 'https://'])) {
            return $logo;
        }
        return \Storage::disk('public')->url($logo);
    }

    public function getIndustryNameAttribute(){
        $industry_id = $this->industry_id;
        if (empty($industry_id)) {
            return '';
        }
        $industry_name = Industry::query()->where('id', $industry_id)->value('name');
        return $industry_name;
    }

    // 所属行业
    public function industry(){
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    // 添加默认企业名片
    public static function addDefaultCompanyCard($user_id, $company_name, $logo){
        $result = self::firstOrCreate(['uid' => $user_id],[
            'company_name' => $company_name,
            'logo' => $logo
        ]);
        Carte::query()->where('uid', $user_id)->update(['cid' => $result->id]);
        return $result;
    }

    /*
     *  行业分类组合
     *  $type = 1 一级分类 2 二级分类
     */
    public function getIndustryArr ($type=1, $industry_id='') {
        if ($type == 1) {
            $result = Industry::where('parent_id', 0)->select('id', 'name')->orderBy('sort','desc')->get();
        } else if ($type == 2 && $industry_id)  {
            $result = Industry::where('parent_id', $industry_id)->select('id', 'name')->orderBy('sort','desc')->get();
        }
        $data = [];
        if (!$result->isEmpty()) {
            foreach ($result  as $value) {
                $data[$value->id] = $value->name;
            }
        }
        return $data;
    }

    // 被关联的名片
    public function cartes(){
        return $this->hasMany(Carte::class, 'cid');
    }

    public function scopeCompanyNameNotEmpay($query){
        return $query->where('company_name', '<>', '');
    }

    public function roles(){
        return $this->belongsToMany(CompanyRole::class, 'company_card_roles', 'company_id', 'role_id');
    }

}
