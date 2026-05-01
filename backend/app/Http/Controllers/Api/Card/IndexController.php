<?php

namespace App\Http\Controllers\Api\Card;

use App\Http\Resources\CardIndexResource;
use App\Models\ApplicationAssociation;
use App\Models\Association;
use App\Models\Carte;
use App\Models\CarteDepartment;
use App\Models\CompanyBind;
use App\Models\CompanyCard;
use App\Models\CompanyCardRole;
use App\Models\CompanyRole;
use App\Models\Configure;
use App\Models\Membership;
use App\Models\User;
use App\Models\User\Attention;
use App\Models\User\Collection;
use App\Models\User\Group;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    // 名片夹主业信息
    public function index(Request $request) {
        $attention = new Attention();
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['uid'] = Auth::id();
        $result = Attention::with(['carte'])
                ->where($where)
                ->where(function ($query) use ($request) {
                    if ($search = $request->input('search','')) {
                        $like = '%'.$search.'%';
                        $query->whereHas('carte',function ($query) use ($like) {
                            $query->where('name', 'like', $like)
                                ->orWhere('company_name', 'like', $like)
                                ->orWhere('position', 'like', $like);
                        });
                    }
                })
                ->orderBy('initial','asc')
                ->get();
        $newData = $this->getNewResult($result);
        $list = $newData['data'] ?? [];
        $num = $newData['num'] ?? 0;
        return new CardIndexResource(compact('list','num'));
    }

    // 个人收藏列表 * 特别关注
    public function specialList(Request $request) {
        $attention = new Attention();
        $user = $request->user();
        $where['uid'] = $user['id'];
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['special'] = $attention::ATTENTION_SPECIAL;
        $list = Attention::with(['carte'])
            ->where($where)
            ->orderBy('special_at', 'desc')
            ->get();
        return new CardIndexResource($list);
    }

    // 联系过列表
    public function contactList(Request $request) {
        $attention = new Attention();
        $user = $request->user();
        $where['uid'] = $user['id'];
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['contacted'] = $attention::ATTENTION_CONTACTED;
        $list = Attention::with(['carte'])
            ->where($where)
            ->orderBy('contact_at', 'desc')
            ->get();
        return new CardIndexResource($list);
    }


    // 通话过列表
    public function talkList(Request $request) {
        $attention = new Attention();
        $user = $request->user();
        $where['uid'] = $user['id'];
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['talked'] = $attention::ATTENTION_TALKED;
        $day = Carbon::parse('-3 day')->toDateTimeString();
        $list = Attention::with(['carte'])
            ->where($where)
            ->where('talk_at', '>', $day)
            ->orderBy('talk_at', 'desc')
            ->get();
        return new CardIndexResource($list);
    }


    // 数据重组
    public function getNewResult($result) {
        // 数据为空直接返回空的数组
        if ($result->isEmpty()) {
            return [];
        }
        $num = 0;
        $newData = [];
        $tem = [];
        $carbon = new Carbon();
        $i = 0;
        foreach ($result as $key => $value) {
            if (in_array($value->initial,$tem)) {
                $newkey = array_search($value->initial,$tem);
                if (!empty($value->carte) && !empty($value->carte->toArray())) {
                    $value->carte->time = $carbon::parse($value->updated_at)->format('Y/m/d');
                    $value->carte->exchange_type = $value->exchange_type;
                    $value->carte->stars = $value->stars;
                    $value->carte->special = $value->special;
                    $newData[$newkey]['datas'][] = $value->carte->toArray();
                    $i++;
                }
            } else {
                $tem[$key] = $value->initial;
                if (!empty($value->carte) && !empty($value->carte->toArray())) {
                    $newData[$key]['alphabet'] = $value->initial;
                    $value->carte->time = $carbon::parse($value->updated_at)->format('Y/m/d');
                    $value->carte->exchange_type = $value->exchange_type;
                    $value->carte->stars = $value->stars;
                    $value->carte->special = $value->special;
                    $newData[$key]['datas'][] = $value->carte->toArray();
                    $i++;
                }
            }

        }
        if (!empty($newData) && $i > 0) {
            $num = $i;
        }
        $realData['data'] = $newData;
        $realData['num'] = $num;
        return $realData;
    }

    public function other(Request $request) {
        $data = [];
        $now = Carbon::now();
        $user = $request->user();
        $carte = Carte::with(['company_card' => function ($query) {
            $query->select('id', 'company_name', 'logo')->where('company_name', '<>', '')->withCount(['carte as carteNum']);
        }])->where(['uid' => Auth::id()])->select('id', 'cid')->first();
        $data['company_name'] = $carte->company_card->company_name ?? '';
        $data['company_logo'] = $carte->company_card->logo ?? '';
        $data['colleague_num'] = $carte->company_card->carteNum ?? 0;
        $group_num = Group::where(['status' => Group::STATUS_NORMAL, 'uid' => Auth::id()])->count();
        $data['group_num'] = $group_num ?? 0;
        $data['carte_id'] = Carte::where(['uid' => Auth::id()])->value('id');

        $data['user'] = $user;
        if (!empty($user->aid)) {
            $associationInfo = Association::query()->where('id', $user->aid)->first();
            $data['society_name'] = $associationInfo->name; // 协会名称
            $data['society_logo'] = $associationInfo->image; // 协会Logo
            $data['society_id'] = $associationInfo->id;
            $data['company_num'] = User::query()->with(['companyCard'])->where('aid', $user->aid)->whereHas('companyCard', function ($query) {
                $query->whereNotNull('company_name');
            })->count();
            $pid = $associationInfo->pid;
            if (!$pid || $pid == 0) {
                $data['application'] = Association::query()->where('pid', $associationInfo->id)->where('id', '<>', $associationInfo->id)
                    ->withCount('associations as company_num')->where('status', Association::STATUS_SUCCESS)->get(['id', 'name', 'image']);
            } else {
                $data['application'] = Association::query()->where('status', Association::STATUS_SUCCESS)->where(function ($query) use ($pid) {
                    $query->where('id', $pid)->orWhere('pid', $pid);
                })->where('id', '<>', $associationInfo->id)->withCount('associations as company_num')->get(['id', 'name', 'image']);
            }
        } else {
            // 平台协会
            $platformAssociation = Association::query()->where('user_id', 0)->first();
            $data['society_name'] = $platformAssociation->name; // 协会名称
            $data['society_logo'] = $platformAssociation->image; // 协会Logo
            $data['society_id'] = $platformAssociation->id;
            // 协会成员数
            $companyIds = CompanyCard::with(['user'])->whereHas('user',function ($query) use ($now) {
                $query->where('enterprise_at', '>=', $now);
            })->where('company_name', '<>', '')->pluck('id')->toArray();

            $data['company_num'] = CompanyCardRole::query()->where('aid', $platformAssociation->id)->whereIn('company_id', $companyIds)->count();

            // 加入的协会
            if ($user->companyCardStatus){
                // 过滤平台的id
//                $aids = CompanyCardRole::query()->where([
//                    'company_id' => $user->companyCard->id,
//                ])->where('aid', '<>', $platformAssociation->id)->pluck('aid')->toArray();

                $aids = CompanyCardRole::query()->where(function (Builder $query) use ($user){
                    $query->where('company_id', $user->companyCard->id)->orWhere('carte_id', $user->carte->id);
                })->where('aid', '<>', $platformAssociation->id)->pluck('aid')->toArray();

                $membershipAids = Membership::query()->where(['user_id' => $user->id, 'status' => Membership::STATUS_PASS])->pluck('aid')->toArray();
                if ($membershipAids) {
                    $aids = array_merge($aids, $membershipAids);
                }

                $data['application'] = Association::query()->whereIn('id', $aids)
                    ->withCount('associations as company_num')->where('status', Association::STATUS_SUCCESS)->get(['id', 'name', 'image']);

            }
        }
        return new CardIndexResource($data);
    }


    public function societyList(Request $request) {
        // 协会id
        $aid = $request->get('aid');
        $roleCompanyList = CompanyRole::query()->where('aid', $aid)->with(['society' => function($query) use ($request){
            $this->bindSocietySearch($query, $request);
        }])->whereHas('society', function($query) use ($request){
            $this->bindSocietySearch($query, $request);
        })
            ->orderBy('sort', 'asc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $companyHasRoleIds = CompanyCardRole::query()->where('aid', $aid)->pluck('company_id')->toArray();

        // 目前加入协会有分配角色id，理论上新加入的成员都有角色
        $generalList = [];
//        $generalList = CompanyCard::query()->with(['user'])->whereHas('user',function ($query) {
//                $query->where('enterprise_at', '>=', Carbon::now());
//            })->where(function ($query) use ($request) {
//                if ($search = $request->input('search','')) {
//                    $like = '%'.$search.'%';
//                    $query->where('company_name', 'like', $like);
//                }
//            })
//            ->whereNotIn('id',$companyHasRoleIds)
//            ->where('status',CompanyCard::TYPE_NORMAL)
//            ->where('company_name', '<>', '')
//            ->select('id', 'uid', 'company_name', 'logo', 'address_title')
////            ->orderBy('role_sort','asc')
//            ->orderBy('updated_at', 'desc')
//            ->get();
        return new CardIndexResource(compact('roleCompanyList', 'generalList'));
    }

    /*
     *  名片夹协会成员列表信息
     */
    public function societyList1(Request $request) {
        $result = CompanyCard::where(function ($query) use ($request) {
                if ($search = $request->input('search','')) {
                    $like = '%'.$search.'%';
                    $query->where('company_name', 'like', $like);
                }
            })
            ->whereHas('user', function($query){
                $query->where('enterprise_at', '>=', Carbon::now());
            })
            ->where('status',CompanyCard::TYPE_NORMAL)
            ->companyNameNotEmpay()
            ->select('id', 'uid', 'company_name', 'logo', 'address_title', 'initial')
            ->orderBy('initial','asc')
            ->get();
        return new CardIndexResource($this->societyNewData($result));
    }

    public function societyNewData($result) {
        $newData = [];
        $tem = [];
        foreach ($result as $key => $value) {
            if (in_array($value->initial,$tem)) {
                $newkey = array_search($value->initial,$tem);
                $newData[$newkey]['datas'][] = $value->toArray();
            } else {
                $tem[$key] = $value->initial;
                $newData[$key]['alphabet'] = $value->initial;
                $newData[$key]['datas'][] = $value->toArray();
            }
        }
        return $newData;
    }

    /*
    *  名片夹公司成员列表信息
    */
    public function companyList() {
        $companyUserId = 0;
        if(Auth::user()->companyCardStatus === true){
            $companyUserId = Auth::user()->id;
        }elseif($carte = Auth::user()->carte){
            $companyBind = CompanyBind::query()->where('carte_id', $carte->id)->with(['company' => function($query){
                $query->with('user');
            }])->where(['status' => CompanyBind::AUDIT_SUCCESS_STATUS])->latest()->first();
            if($companyBind && $companyBind->company && $companyBind->company->user){
                $companyUserId = $companyBind->company->user->id;
            }
        }
        $result = Carte::with(['company_card' => function ($query) use ($companyUserId) {
                $query->select('id', 'uid', 'logo', 'company_name')->with(['carte' => function($query) use ($companyUserId) {
                    $query->with(['carteDepartments' => function($query) use ($companyUserId) {
                        $query->with(['department' => function($query) use ($companyUserId){
                            $query->where('uid', $companyUserId);
                        }]);
                    }]);
                }]);
            }])
            ->where(function ($query) {
                $query->whereHas('company_card');
            })
            ->where('uid', Auth::id())
            ->select('id', 'cid')
            ->first();

        return new CardIndexResource($result->company_card);
    }

    // 收藏夹列表
    public function getCollection(Request $request){
        $attention = new Attention();
        $where['status'] = $attention::ATTENTION_STATUS_ONE;
        $where['uid'] = Auth::id();
        // 过滤没有经纬度的名片
        $list = Attention::with(['carte'])
            ->where($where)
            ->whereHas('carte', function ($query){
                $query->where(function($query){
                    $query->whereNotNull('longitude')->whereNotNull('latitude');
                })->orWhere(function($query){
                    $query->where('longitude', '<>', '')->where('latitude', '<>', '');
                });
            })
            ->get();

        return new CardIndexResource(compact('list'));
    }

    private function bindSocietySearch($query, $request){
        if ($search = $request->input('search','')) {
            $like = '%'.$search.'%';
            $query->where('company_name', 'like', $like);
        }else{
            $query->where('company_name', '<>', '');
        }
    }

}
