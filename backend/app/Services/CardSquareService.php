<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/29
 * Time: 15:35
 */

namespace App\Services;

use App\Models\Carte;
use App\Models\CarteVisits;
use App\Models\CompanyCard;
use App\Models\Industry;
use App\Models\User;
use App\Models\UserScreen;
use Carbon\Carbon;
use  Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CardSquareService
{
    public function getList(Request $request){
        $searchType = $request->get('searchType', 'default');
        if($searchType === 'custom' && $request->get('params')){
            $params = $request->get('params');
            if($params){
                $cardSquares = $this->customSearch($params, $request->except(['params', 'searchType']));
            }else{
                abort(403, '请求错误');
            }
        }else{
            $cardSquares = $this->defaultSearch($request);
        }

        return $cardSquares;
    }


    // 默认搜索
    public function defaultSearch(Request $request){

//        $query = Carte::query()->with(['user' => function($query){
//            $query->with('companyCard');
//        }, 'industry'])->where('open', Carte::OPEN_ONE)->where('uid', '<>', 0)
//            ->where('name', '<>', '')->where('company_name', '<>', '')->where('phone', '<>', '');

        $query = $this->baseCondition(Carte::query());

        $append = [];
        $param = $request->all();
        $this->baseSearch($query, $param, $append);
        return $query->paginate()->appends($append);
    }

    // 定制搜索
    public function customSearch(array $params, array $other){


        $query = $this->baseCondition(Carte::query());


        $append = [
            'params' => [],
            'searchType' => 'custom',
        ];




        // 处理联合
        $model = null;
        if(!empty(array_filter($params)) || !empty($other)){
            $query = Carte::query()->where('id', 0);
            if(empty(array_filter($params)) && !empty($other)){
                $index = 10000;
                $subQuery[$index] = $this->baseCondition(Carte::query());
                $this->baseSearch($subQuery[$index], $other, $append);
                $query->union($subQuery[$index]);
            }
            $subQuery = [];
            foreach ($params as $k => $v){
                if (empty($v)) {
                    continue;
                }
                foreach ($v as $key => $param){
                    $index = mt_rand(100, 9999) + $key;
                    // 删除行业的name字段
                    if(isset($param['name'])){
                        unset($param['name']);
                    }
                    $param = array_merge($param, $other);
                    $subQuery[$index] = $this->baseCondition(Carte::query());
                    $this->baseSearch($subQuery[$index], $param, $append);
                    $query->union($subQuery[$index]);
                }
            }


//            $carteQuery = Carte::query()->where('open', Carte::OPEN_ONE)->where('uid', '<>', 0)
//                ->where('name', '<>', '')->where('company_name', '<>', '')->where('phone', '<>', '')->with(['user' => function($query){
//                $query->with('companyCard');
//            }, 'industry'])->latest()->orderBy('id', 'desc');
//
//            if($model){
//                return $carteQuery->mergeBindings($model->getQuery())
//                    ->from(DB::raw("({$model->toSql()}) as assets_device"))
//                    ->paginate()->appends($append);
//            }

            return $query->paginate()->appends($append);

        }
        return $query->latest()->orderBy('id', 'desc')->paginate()->appends($append);
    }


    private function baseCondition($query){
        $query->with(['user' => function($query){
            $query->with('companyCard');
        }, 'industry'])->where('open', Carte::OPEN_ONE)->where('uid', '<>', 0)
            ->where('name', '<>', '')->where('company_name', '<>', '')->where('phone', '<>', '');
        return $query;
    }


    // 组装搜索条件
    private function baseSearch($query, array $param, &$append){
        // 关键字查找
        $condition = [];
        if(isset($param['keyword'])){
            $keyword = $param['keyword'];
            if(!empty($keyword)){
                $query->where(function($query) use ($keyword){
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('company_name',  'like', '%' . $keyword . '%')
                        ->orWhere('position', 'like', '%' . $keyword . '%')
                        ->orWhere(function($query) use ($keyword){
                            $cids = CompanyCard::where('company_name',  'like', '%' . $keyword . '%')->pluck('id')->toArray();
                            if($cids){
                                $query->whereIn('cid', $cids);
                            }
                        });
                });
                $condition['keyword'] = $keyword;
            }
        }


        if(isset($param['attestation'])){
            // 是否认证
            $attestation = $param['attestation'];
            if(!empty($attestation) && boolval($attestation) === true){
                $condition['attestation'] = true;
                $query->whereHas('user', function($q){
                    $q->where('type', User::USER_TYPE_TWO);
                });
            }
        }


        if(isset($param['industry_id'])){
            // 所属行业
            $industry_id = $param['industry_id'];
            if(!empty($industry_id)){
                if(strpos($industry_id, '-') !== false){
                    $idLimits = explode('-', $industry_id);
                    if($idLimits && $idLimits[0] > 0 && $idLimits[1] == 0){
                        // 父级不默认，子级默认，找出所有下级
                        $industryIds = Industry::query()->where('parent_id', $idLimits[0])->pluck('id')->toArray();
                        $query->whereIn('industry_id', $industryIds);
                    }
                }
                if(is_numeric($industry_id)){
                    $query->where('industry_id', $industry_id);
                }

            }
            if(!empty($industry_id)){
                $condition['industry_id'] = $industry_id;
            }
        }



        if(isset($param['select_province']) && !empty($param['select_province'])){
            // 所在省份
            $province = $param['select_province'];
            if(!empty($province)){
                $query->where('province', $province);
                $condition['province'] = $province;
            }
        }

        if(isset($param['province']) && !empty($param['province'])){
            // 所在省份
            $province = $param['province'];
            if(!empty($province)){
                $query->where('province', $province);
                $condition['province'] = $province;
            }
        }


        if(isset($param['select_city']) && !empty($param['select_city'])){
            // 所在城市
            $city = $param['select_city'];
            if(!empty($city)){
                $query->where('city', $city);
                $condition['city'] = $city;
            }
        }

        if(isset($param['city']) && !empty($param['city'])){
            // 所在城市
            $city = $param['city'];
            if(!empty($city)){
                $query->where('city', $city);
                $condition['city'] = $city;
            }
        }


        if(isset($param['sort']) && !empty($param['sort'])){
            // 排序方式
            $sort = $param['sort'];
            $condition['sort'] = 'latest';
            if(!empty($sort) && in_array($sort, ['latest', 'nearby'])){
                switch ($sort) {
                    case 'latest':
                        $query->latest()->orderBy('id', 'desc');
                        break;
                    case 'nearby':
                        $latitude = $param['latitude']; // 纬度
                        $longitude = $param['longitude'];// 经度
                        if($latitude && $longitude){
                            $query->select(DB::raw('*, ROUND((ACOS(SIN(('. $latitude .' * 3.1415) / 180 ) *SIN((latitude* 3.1415) / 180 ) +
COS(('. $latitude .'  * 3.1415) / 180 ) * COS((latitude* 3.1415) / 180 ) *COS(('. $longitude .' * 3.1415) / 180
- (longitude* 3.1415) / 180 ) ) * 6380),2) as distance'))->orderBy('distance');
                        }
                        $append['sort'] = 'nearby';
                        $append['latitude'] = $latitude;
                        $append['longitude'] = $longitude;
                        break;
                }
            }else{
                // 默认按最新排序
                $query->latest()->orderBy('id', 'desc');
            }
        }else{
            if(!isset($append['params'])){
                $query->latest()->orderBy('id', 'desc');
            }
        }
        if(isset($append['params'])){
           // $condition['is_active'] = (isset($param['is_active']) && $param['is_active']) === true ? true : false;
            $append['params'][] = $param;
        }else{
            $append = $condition;
        }
        return $query;
    }


    // 浏览
    public function visits(Carte $carte, Request $request){
        $user = $request->user('api');
        $user_id = 0;
        if($user){
            $user_id = $user->id;
        }
        // 如果当前浏览者是本人，不操作
        if($user_id === $carte->uid){
            return;
        }
        // 浏览量+1 (本人不增加浏览量)
        $carte->increment('visits');
        $carte->increment('new_visits');
        //' 如果当前未登录，无需判断浏览记录
        if($user_id === 0){
            CarteVisits::addCarteVisits($carte->id, $user_id);
            return;
        }
        $cateVisits = CarteVisits::where(['user_id' =>  $user_id, 'carte_id' => $carte->id])->first();

        if($cateVisits){
            // 更新时间
            $cateVisits->last_view_time = Carbon::now();
        }else{
            $cateVisits = CarteVisits::addCarteVisits($carte->id, $user_id);
        }

        /**
         * 上一次访问时间
         * @var $last_view_time Carbon
         */
        $last_view_time = $cateVisits->last_view_time;

        // 本周处理
        if(CarteVisits::isWeekEnd($last_view_time)){
            // 本周访问数改为1
            $cateVisits->week_nums = 1;
        }else{
            $cateVisits->week_nums += 1;
        }
        $cateVisits->save();
        return;
    }
}
