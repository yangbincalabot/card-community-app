<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CompanyRole extends Model
{
    protected $table = 'company_role';

    protected $guarded = ['id'];

    public static function addRole($uid, $name, $sort, $fee = 0) {
        return self::query()->create([
            'uid' => $uid,
            'name' => $name,
            'sort' => $sort,
            'fee' => $fee,
        ]);
    }

//    public function companys() {
//        return $this->hasMany(CompanyCard::class, 'role_id')
//            ->with(['user'])->whereHas('user',function ($query) {
//                $query->where('enterprise_at', '>=', Carbon::now());
//            })
//            ->select('id', 'uid', 'role_id', 'role_sort','company_name', 'logo', 'address_title')
//            ->orderBy('role_sort', 'asc')
//            ->orderBy('updated_at', 'desc');
//    }



    public function companys(){
       return $this->belongsToMany(CompanyCard::class, 'company_card_roles', 'role_id', 'company_id')->withPivot('id','role_sort');
    }

    public function society(){
        return $this->belongsToMany(CompanyCard::class, 'company_card_roles', 'role_id', 'company_id')
            ->with(['user'])->whereHas('user',function ($query) {
                $query->where('enterprise_at', '>=', Carbon::now());
            })
            ->select('company_card.id', 'uid', 'company_name', 'logo', 'address_title')  // 混淆而报错，id需要加表明
            ->orderBy('company_card_roles.role_sort', 'asc') // // 混淆而报错，role_sort需要加表明
            ->orderBy('updated_at', 'desc');
    }

    public function cartes() {
        return $this->belongsToMany(Carte::class, 'company_card_roles', 'role_id', 'carte_id')->withPivot('id','role_sort');
    }

    // 关联协会
    public function aid(){
        return $this->belongsTo(Association::class, 'aid');
    }

}
